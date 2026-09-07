<?php

declare(strict_types=1);

namespace Kumwe\Computation\Tests;

use InvalidArgumentException;
use JsonSerializable;
use Kumwe\CanonicalJson\CanonicalEncoder;
use Kumwe\CanonicalJson\Limits;
use Kumwe\Computation\CapabilitySet;
use Kumwe\Computation\CompiledProgram;
use Kumwe\Computation\Compiler;
use Kumwe\Computation\ConfigProvider;
use Kumwe\Computation\ContractIdentity;
use Kumwe\Computation\DocumentBatch;
use Kumwe\Computation\DocumentInput;
use Kumwe\Computation\ExecutionLimits;
use Kumwe\Computation\ExecutionRefused;
use Kumwe\Computation\Executor;
use Kumwe\Computation\Internal\Guard;
use Kumwe\Computation\NativeAdapterFactory;
use Kumwe\Computation\NativeAdapter;
use Kumwe\Computation\NativeCanonicalEncoder;
use Kumwe\Computation\NativeCanonicalEncoderFactory;
use Kumwe\Computation\NativeCompatibility;
use Kumwe\Computation\PlanIdentity;
use Kumwe\Computation\ProgramEnvelope;
use Kumwe\Computation\RefusalCode;
use Psr\Container\ContainerInterface;
use RuntimeException;

$arguments = $argv ?? [];
if (in_array('--list-json', $arguments, true)) {
    echo json_encode([
        'native adapter and canonical integration through actual extension' => 'tests/native.php',
    ], JSON_THROW_ON_ERROR) . "\n";
    exit(0);
}

require $arguments[2] ?? dirname(__DIR__) . '/vendor/autoload.php';

if (!extension_loaded('kumwe_engine')) {
    throw new RuntimeException('The native integration gate requires the actual kumwe_engine extension.');
}
if (count($arguments) !== 2 && count($arguments) !== 3) {
    throw new RuntimeException('Supply the configured compatibility JSON and an optional consumer autoloader.');
}
$bytes = file_get_contents($arguments[1]);
if (!is_string($bytes)) {
    throw new RuntimeException('Candidate compatibility input is unavailable.');
}
$expected = Guard::object(json_decode($bytes, true, 64, JSON_THROW_ON_ERROR));
$compatibility = new NativeCompatibility(
    CapabilitySet::fromArray(Guard::object($expected['capabilities'] ?? null)),
    Guard::token($expected['extension_version'] ?? null),
    Guard::token($expected['embedded_engine_commit'] ?? null),
    Guard::digest($expected['embedded_source_sha256'] ?? null),
);
$container = new class ($compatibility) implements ContainerInterface {
    /**
     * @param mixed $compatibility Candidate configuration, including deliberately invalid service types.
     * @param mixed $canonicalLimits Optional canonical budget service, including invalid test values.
     * @param bool $hasCanonicalLimits Whether this fixture explicitly supplies a budget service.
     * @since 0.2.0
     */
    public function __construct(
        private mixed $compatibility,
        private mixed $canonicalLimits = null,
        private bool $hasCanonicalLimits = false,
    ) {
    }

    /**
     * @param string $id Requested service.
     * @return mixed Test configuration service.
     * @since 0.2.0
     */
    public function get(string $id): mixed
    {
        if ($id === NativeCompatibility::class) {
            return $this->compatibility;
        }
        if ($id === Limits::class && $this->hasCanonicalLimits) {
            return $this->canonicalLimits;
        }
        throw new RuntimeException('Unconfigured test service.');
    }

    /**
     * @param string $id Requested service.
     * @return bool Whether supplied by this test container.
     * @since 0.2.0
     */
    public function has(string $id): bool
    {
        return $id === NativeCompatibility::class || ($id === Limits::class && $this->hasCanonicalLimits);
    }
};
$assertions = 0;
$that = static function (bool $condition, string $message) use (&$assertions): void {
    if (!$condition) {
        throw new RuntimeException($message);
    }
    ++$assertions;
};
$refuses = static function (callable $operation, RefusalCode $reason) use ($that): void {
    try {
        $operation();
    } catch (ExecutionRefused $failure) {
        $that($failure->reason === $reason, 'Wrong portable refusal category.');
        $that($failure->getPrevious() === null, 'Native payload exception must not be retained.');
        return;
    }
    throw new RuntimeException('Expected a native refusal.');
};
$contract = static function (string $profile) use ($compatibility): ContractIdentity {
    foreach ($compatibility->capabilities->contracts() as $contract) {
        if ($contract->profile === $profile) {
            return $contract;
        }
    }
    throw new RuntimeException('The configured native profile is missing.');
};
$identity = static fn (ProgramEnvelope $program): PlanIdentity => new PlanIdentity(
    $program->contract,
    $program->programVersion,
    $program->digest(),
    'test-generation',
    $program->digest(),
    hash('sha256', 'test-schema'),
    hash('sha256', 'test-options'),
    $compatibility->capabilities,
);
$limits = new ExecutionLimits(maxDocuments: 8);
$provider = (new ConfigProvider())();
$that($provider === ['dependencies' => [
    'factories' => [
        NativeAdapter::class => NativeAdapterFactory::class,
        NativeCanonicalEncoder::class => NativeCanonicalEncoderFactory::class,
    ],
    'aliases' => [
        Compiler::class => NativeAdapter::class,
        Executor::class => NativeAdapter::class,
        CanonicalEncoder::class => NativeCanonicalEncoder::class,
    ],
    'shared' => [NativeAdapter::class => true, NativeCanonicalEncoder::class => true],
]], 'Provider service identities or request-scoped sharing changed.');
$badCompatibilityService = new ($container::class)('not-a-compatibility-service');
$refuses(static fn () => (new NativeAdapterFactory())($badCompatibilityService), RefusalCode::IncompatibleCapability);
$badLimitsService = new ($container::class)($compatibility, 'unbounded', true);
$refuses(
    static fn () => (new NativeCanonicalEncoderFactory())($badLimitsService),
    RefusalCode::IncompatibleCapability,
);
$wrongTuple = new NativeCompatibility(
    $compatibility->capabilities,
    'different-extension',
    $compatibility->embeddedEngineCommit,
    $compatibility->embeddedSourceSha256,
);
$wrongTupleContainer = new ($container::class)($wrongTuple);
$refuses(static fn () => (new NativeAdapterFactory())($wrongTupleContainer), RefusalCode::IncompatibleCapability);
$adapter = (new NativeAdapterFactory())($container);
$formula = $contract('formula-draft/1');
$source = new ProgramEnvelope(
    $formula,
    '0.0.0',
    '{"op":"add","type":"integer","args":['
        . '{"op":"field","type":"integer","field":"n"},'
        . '{"op":"literal","type":"integer","value":1}]}',
);
$plan = $adapter->compile($source, $identity($source), $limits);
$refuses(
    static fn () => $adapter->compile($source, $identity($source), new ExecutionLimits(maxInstructions: 1)),
    RefusalCode::ExhaustedLimit,
);
$documents = new DocumentBatch([
    new DocumentInput('first', $formula, '{"fields":{"n":4},"lines":{}}'),
    new DocumentInput('second', $formula, '{"fields":{"n":9},"lines":{}}'),
], $limits);
$results = $adapter->execute($plan, $documents, $limits)->results();
$that($results[0]->bytes === '{"findings":[],"value":5}', 'First exact native result bytes changed.');
$that($results[1]->bytes === '{"findings":[],"value":10}', 'Batch order or second result changed.');
$that($results[0]->findings() === [], 'Formula unexpectedly returned findings.');
$other = (new NativeAdapterFactory())($container);
$refuses(static fn () => $other->execute($plan, $documents, $limits), RefusalCode::InvalidProgram);
$copy = CompiledProgram::fromArray($plan->toArray(), $source, $limits);
$refuses(static fn () => $adapter->execute($copy, $documents, $limits), RefusalCode::InvalidProgram);
$refuses(static fn () => clone $adapter, RefusalCode::InvalidProgram);
$underflow = new ProgramEnvelope($formula, '0.0.0', '{"op":"literal","type":"integer","value":1e-400}');
$refuses(
    static fn () => $adapter->compile($underflow, $identity($underflow), $limits),
    RefusalCode::InvalidProgram,
);
$badInput = new DocumentBatch([
    new DocumentInput('underflow', $formula, '{"fields":{"n":1e-400},"lines":{}}'),
], $limits);
$refuses(static fn () => $adapter->execute($plan, $badInput, $limits), RefusalCode::InvalidInput);
$refuses(
    static fn () => $adapter->execute($plan, $documents, new ExecutionLimits(maxInstructions: 1)),
    RefusalCode::ExhaustedLimit,
);
$that(count($adapter->execute($plan, $documents, $limits)->results()) === 2, 'Plan reuse after refusal failed.');
$documentContract = $contract('normalized-document-draft/1');
$documentSource = new ProgramEnvelope(
    $documentContract,
    '0.0.0',
    '{"fields":[{"handle":"name","required":true,"nullable":false}],"invariants":[]}',
);
$documentPlan = $adapter->compile($documentSource, $identity($documentSource), $limits);
$documentInputs = new DocumentBatch([
    new DocumentInput('required', $documentContract, '{"fields":{},"lines":{}}'),
], $limits);
$documentResult = $adapter->execute($documentPlan, $documentInputs, $limits)->results()[0];
$that(str_contains($documentResult->bytes, '"values":{}'), 'Opaque native empty-object identity changed.');
$that(count($documentResult->findings()) === 1, 'Native portable finding was dropped.');
$that($documentResult->findings()[0]->code === 'required', 'Native finding code changed.');
$that($documentResult->findings()[0]->path->segments() === ['name'], 'Native finding path changed.');
$that($documentResult->findings()[0]->ordinal === 0, 'Native finding order changed.');
$canonical = (new NativeCanonicalEncoderFactory())($container);
$that($canonical->encode([1 => 'b', 0 => 'a']) === '["a","b"]', 'Native PHP-key list reclassification failed.');
$that($canonical->encode(-0.0) === '-0.0', 'Native negative zero was lost.');
$that(
    $canonical->digest(null) === '74234e98afe7498fb5daf1f36ac2d78acc339464f950703b8c019892f982b90b',
    'Native incremental SHA-256 differed from the frozen vector.',
);
$finding = static function (callable $operation, string $code) use ($that): void {
    try {
        $operation();
    } catch (InvalidArgumentException $failure) {
        $that($failure->getMessage() === $code, 'Canonical finding identity changed.');
        return;
    }
    throw new RuntimeException('Expected a canonical finding.');
};
$tightContainer = new ($container::class)($compatibility, new Limits(maxOutputBytes: 2), true);
$tightCanonical = (new NativeCanonicalEncoderFactory())($tightContainer);
$finding(static fn () => $tightCanonical->encode('x'), 'canonical.output-limit');
$that($tightCanonical->encode(0) === '0', 'Tight canonical service did not recover after output refusal.');
$finding(static fn () => $canonical->encode(INF), 'canonical.non-finite-number');
$finding(static fn () => $canonical->encode("\xff"), 'canonical.invalid-utf8');
$object = new class implements JsonSerializable {
    /**
     * @return mixed Callback must never be reached by canonical native transport.
     * @since 0.2.0
     */
    public function jsonSerialize(): mixed
    {
        throw new RuntimeException('Payload callback was invoked.');
    }
};
$finding(static fn () => $canonical->encode($object), 'canonical.unsupported-type');
$that($canonical->encode('reused') === '"reused"', 'Canonical service did not recover after refusals.');
echo 'Native adapter integration: ' . $assertions . " assertions passed.\n";
