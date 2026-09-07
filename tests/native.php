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
    Guard::digest($expected['binding_build_digest'] ?? null),
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
    $compatibility->bindingBuildDigest,
);
$wrongTupleContainer = new ($container::class)($wrongTuple);
$refuses(static fn () => (new NativeAdapterFactory())($wrongTupleContainer), RefusalCode::IncompatibleCapability);
$wrongBuildTuple = new NativeCompatibility(
    $compatibility->capabilities,
    $compatibility->extensionVersion,
    $compatibility->embeddedEngineCommit,
    $compatibility->embeddedSourceSha256,
    hash('sha256', $compatibility->bindingBuildDigest),
);
$wrongBuildContainer = new ($container::class)($wrongBuildTuple);
$refuses(static fn () => (new NativeAdapterFactory())($wrongBuildContainer), RefusalCode::IncompatibleCapability);
$refuses(
    static fn () => (new NativeCanonicalEncoderFactory())($wrongBuildContainer),
    RefusalCode::IncompatibleCapability,
);
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
$validatorSource = new ProgramEnvelope(
    $documentContract,
    '0.0.0',
    '{"fields":['
        . '{"handle":"label","validators":[{"rule":"pattern","value":"^[\\\\p{L}]+-[0-9]{2}$"}]},'
        . '{"handle":"amount","type":"core.decimal","precision":6,"scale":2,'
        . '"validators":[{"rule":"decimal"},{"rule":"min","value":"10.00"}]}],"invariants":[]}',
);
$validatorPlan = $adapter->compile($validatorSource, $identity($validatorSource), $limits);
$validatorInputs = new DocumentBatch([
    new DocumentInput(
        'valid',
        $documentContract,
        '{"fields":{"label":"Ångström-42","amount":{"type":"exact-decimal","value":"12.30"}},"lines":{}}',
    ),
    new DocumentInput('invalid', $documentContract, '{"fields":{"label":"bad 42","amount":"12.30"},"lines":{}}'),
], $limits);
$validatorResults = $adapter->execute($validatorPlan, $validatorInputs, $limits)->results();
$that($validatorResults[0]->findings() === [], 'Unicode PCRE2 or exact-decimal native validation changed.');
$that(str_contains($validatorResults[0]->bytes, '"amount":"12.30"'), 'Exact decimal scale was not retained.');
$that(count($validatorResults[1]->findings()) === 2, 'Invalid normalized inputs lost their findings.');
$that($validatorResults[1]->findings()[0]->code === 'pattern', 'Pattern finding order changed.');
$that($validatorResults[1]->findings()[1]->code === 'decimal', 'Raw string was treated as an exact decimal.');
$normalizedSource = new ProgramEnvelope(
    $documentContract,
    '0.0.0',
    '{"fields":[{"handle":"money"},{"handle":"occurred"}],"invariants":[]}',
);
$normalizedPlan = $adapter->compile($normalizedSource, $identity($normalizedSource), $limits);
$normalizedBytes = '{"fields":{'
    . '"money":{"type":"normalized-value","version":1,"kind":"money",'
    . '"value":{"amount":"12.30","currency":"USD"}},'
    . '"occurred":{"type":"normalized-value","version":1,"kind":"datetime",'
    . '"value":"2026-09-07T10:11:12.123456+02:00"}},"lines":{}}';
$normalizedInputs = new DocumentBatch([
    new DocumentInput('normalized', $documentContract, $normalizedBytes),
], $limits);
$normalizedResult = $adapter->execute($normalizedPlan, $normalizedInputs, $limits)->results()[0];
$that(
    $normalizedResult->bytes === '{"findings":[],"values":{'
        . '"money":{"amount":"12.30","currency":"USD"},"occurred":"2026-09-07T10:11:12.123456+02:00"}}',
    'Versioned normalized money and datetime storage lost native result identity.',
);
$unknownNormalizedVersion = new DocumentBatch([
    new DocumentInput(
        'unknown-version',
        $documentContract,
        str_replace('"version":1', '"version":2', $normalizedBytes),
    ),
], $limits);
$refuses(
    static fn () => $adapter->execute($normalizedPlan, $unknownNormalizedVersion, $limits),
    RefusalCode::InvalidInput,
);
$that(
    $adapter->execute($normalizedPlan, $normalizedInputs, $limits)->results()[0]->bytes === $normalizedResult->bytes,
    'Normalized-value plan did not recover after refusing an unknown wire version.',
);
$preparation = $contract('normalized-preparation-draft/1');
$preparationSource = new ProgramEnvelope(
    $preparation,
    '0.0.0',
    '{"fields":['
        . '{"handle":"enabled","identity":false,"sequence":false,"computed":false,"server_only":false,'
        . '"read_only":false,"immutable_after_create":false,"default":{"value":true,"valid":true},'
        . '"visibility_condition":null,"editability_condition":null},'
        . '{"handle":"name","identity":false,"sequence":false,"computed":false,"server_only":false,'
        . '"read_only":false,"immutable_after_create":false,"default":{"value":"default","valid":true},'
        . '"visibility_condition":null,"editability_condition":{"op":"field","type":"boolean","field":"enabled"}}],'
        . '"validation":{"fields":[{"handle":"enabled"},{"handle":"name"}],"invariants":[]}}',
);
$preparationPlan = $adapter->compile($preparationSource, $identity($preparationSource), $limits);
$preparationInputs = new DocumentBatch([
    new DocumentInput(
        'create',
        $preparation,
        '{"fields":{"operation":"create","current":{},"input":[],"identity":"host-id","allocated":{}},"lines":{}}',
    ),
    new DocumentInput(
        'update',
        $preparation,
        '{"fields":{"operation":"update","current":{"enabled":true,"name":"before"},"input":['
            . '{"handle":"enabled","submitted":false,"normalized":{"value":false,"valid":true}},'
            . '{"handle":"name","submitted":"after","normalized":{"value":"after","valid":true}}],'
            . '"identity":"host-id","allocated":{}},"lines":{}}',
    ),
], $limits);
$preparationResults = $adapter->execute($preparationPlan, $preparationInputs, $limits)->results();
$that(
    $preparationResults[0]->bytes === '{"findings":[],"values":{"enabled":true,"name":"default"}}',
    'Native create preparation did not apply the supplied normalized default.',
);
$that(
    $preparationResults[1]->bytes === '{"findings":[],"values":{"enabled":false,"name":"after"}}',
    'Native update editability must use the prior record before the ordered input patch.',
);
$report = $contract('report-materialization-draft/1');
$converted = [
    'converted_money' => 'EUR 1.25 converted from USD 1.00',
    'converted_quantity' => '1.25 kg converted from 1.00 lb',
];
foreach ($converted as $type => $prefix) {
    $value = $prefix
        . ' at 1.245 as at 2026-08-14T00:00:00.000000+00:00 by acme.rates rounded half_up from 1.24500';
    $reportSource = new ProgramEnvelope(
        $report,
        '0.0.0',
        '{"columns":[{"alias":"source","source":"source","type":"string"}],"groups":[],"aggregates":[],'
            . '"formulas":[{"alias":"converted","type":"' . $type . '",'
            . '"expression":{"op":"field","type":"any","field":"source"}}],"sorts":[]}',
    );
    $reportPlan = $adapter->compile($reportSource, $identity($reportSource), $limits);
    $reportInputs = new DocumentBatch([
        new DocumentInput($type, $report, '{"fields":{"rows":[{"source":"' . $value . '"}]},"lines":{}}'),
    ], $limits);
    $reportResults = $adapter->execute($reportPlan, $reportInputs, $limits)->results();
    $that($reportResults[0]->findings() === [], 'Valid converted value produced native report findings.');
    $that(
        $reportResults[0]->bytes
            === '{"findings":[],"rows":[{"converted":"' . $value . '","source":"' . $value . '"}]}',
        'Converted report canonical value or exact provenance changed.',
    );
    $invalidReport = new DocumentBatch([
        new DocumentInput(
            'bad-rounding',
            $report,
            '{"fields":{"rows":[{"source":"' . str_replace('1.25', '1.24', $value) . '"}]},"lines":{}}',
        ),
    ], $limits);
    $refuses(static fn () => $adapter->execute($reportPlan, $invalidReport, $limits), RefusalCode::InvalidInput);
    $that(
        $adapter->execute($reportPlan, $reportInputs, $limits)->results()[0]->bytes === $reportResults[0]->bytes,
        'Converted report plan did not recover after arithmetic refusal.',
    );
}
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
