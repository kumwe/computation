<?php

declare(strict_types=1);

namespace Kumwe\Computation\Tests;

use Kumwe\Computation\BatchResult;
use Kumwe\Computation\CapabilitySet;
use Kumwe\Computation\CompatibilityRequirement;
use Kumwe\Computation\CompiledProgram;
use Kumwe\Computation\Compiler;
use Kumwe\Computation\ContractIdentity;
use Kumwe\Computation\DocumentBatch;
use Kumwe\Computation\DocumentInput;
use Kumwe\Computation\ExecutionLimits;
use Kumwe\Computation\ExecutionRefused;
use Kumwe\Computation\ExecutionResult;
use Kumwe\Computation\Executor;
use Kumwe\Computation\Finding;
use Kumwe\Computation\FindingPath;
use Kumwe\Computation\FindingSeverity;
use Kumwe\Computation\Internal\Guard;
use Kumwe\Computation\PlanCacheKey;
use Kumwe\Computation\PlanIdentity;
use Kumwe\Computation\ProgramEnvelope;
use Kumwe\Computation\RefusalCode;
use Kumwe\Computation\SourceLocation;
use RuntimeException;
use Throwable;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Meaningful portable boundary assertions, excluded from the runtime archive.
 * @since 0.1.0
 */
final class Check
{
    /**
     * @var int Completed assertions.
     * @since 0.1.0
     */
    public static int $assertions = 0;
    /**
     * @var int Completed test groups.
     * @since 0.1.0
     */
    public static int $tests = 0;

    /**
     * @param bool $condition Expected truth.
     * @return void
     * @since 0.1.0
     */
    public static function that(bool $condition): void
    {
        self::$assertions++;
        if (!$condition) {
            throw new RuntimeException('Boundary assertion failed.');
        }
    }

    /**
     * @param callable():mixed $operation Hostile operation; any returned value is discarded.
     * @param RefusalCode $reason Expected refusal.
     * @return void
     * @since 0.1.0
     */
    public static function refuses(callable $operation, RefusalCode $reason = RefusalCode::InvalidInput): void
    {
        try {
            $operation();
        } catch (ExecutionRefused $error) {
            self::that($error->reason === $reason);
            self::that(strlen($error->getMessage()) < 100 && $error->getPrevious() === null);
            return;
        }
        throw new RuntimeException('An invalid boundary input was accepted.');
    }

    /**
     * @param string $name Responsibility.
     * @param callable():void $operation Assertions.
     * @return void
     * @since 0.1.0
     */
    public static function test(string $name, callable $operation): void
    {
        try {
            $operation();
            self::$tests++;
            echo 'PASS ' . $name . "\n";
        } catch (Throwable $error) {
            fwrite(STDERR, 'FAIL ' . $name . ': ' . $error->getMessage() . "\n" . $error->getTraceAsString() . "\n");
            exit(1);
        }
    }
}

/**
 * Synthetic transport fixtures; never represent a released semantic or native runtime.
 * @since 0.1.0
 */
final class Fixture
{
    /**
     * @return ContractIdentity Synthetic exact profile.
     * @since 0.1.0
     */
    public static function contract(): ContractIdentity
    {
        return new ContractIdentity('example/transport', 'test-only', '0.1.0', str_repeat('a', 64));
    }

    /**
     * @return CapabilitySet Synthetic observation for metadata tests.
     * @since 0.1.0
     */
    public static function capabilities(): CapabilitySet
    {
        return new CapabilitySet(
            '0.1.0',
            1,
            '1.0.0',
            str_repeat('b', 64),
            ['document.batch' => 1, 'compile' => 1],
            [self::contract()]
        );
    }

    /**
     * @return ProgramEnvelope Opaque source, without AST semantics.
     * @since 0.1.0
     */
    public static function source(): ProgramEnvelope
    {
        return new ProgramEnvelope(self::contract(), '1.0.0', 'opaque-source');
    }

    /**
     * @return PlanIdentity Complete synthetic plan identity.
     * @since 0.1.0
     */
    public static function plan(): PlanIdentity
    {
        return new PlanIdentity(
            self::contract(),
            '1.0.0',
            self::source()->digest(),
            'generation.1',
            str_repeat('c', 64),
            str_repeat('d', 64),
            str_repeat('e', 64),
            self::capabilities()
        );
    }

    /**
     * @return CompiledProgram Opaque artifact for transport testing only.
     * @since 0.1.0
     */
    public static function compiled(): CompiledProgram
    {
        return new CompiledProgram(
            self::source(),
            self::plan(),
            'fixture.artifact.1',
            'opaque-artifact',
            new ExecutionLimits()
        );
    }

    /**
     * @param string $correlation Document identity.
     * @return DocumentInput Opaque document.
     * @since 0.1.0
     */
    public static function input(string $correlation = 'document.1'): DocumentInput
    {
        return new DocumentInput($correlation, self::contract(), 'opaque-document');
    }

    /**
     * @param int $ordinal Stable finding order.
     * @return Finding Machine finding.
     * @since 0.1.0
     */
    public static function finding(int $ordinal = 0): Finding
    {
        return new Finding(
            'required',
            FindingSeverity::Error,
            new FindingPath(['lines', 0, 'amount']),
            new SourceLocation('program', 'rule.required', 0),
            $ordinal,
            ['field' => 'amount']
        );
    }
}

/**
 * Test-only port fake returns fixed fixture data and executes no semantic algorithms.
 * @since 0.1.0
 */
final class PortFake implements Compiler, Executor
{
    /**
     * @param ProgramEnvelope $program Source.
     * @param PlanIdentity $plan Identity.
     * @param ExecutionLimits $limits Budgets.
     * @return CompiledProgram
     * @since 0.1.0
     */
    public function compile(ProgramEnvelope $program, PlanIdentity $plan, ExecutionLimits $limits): CompiledProgram
    {
        return new CompiledProgram($program, $plan, 'fixture.artifact.1', 'fixed-artifact', $limits);
    }

    /**
     * @param CompiledProgram $program Artifact.
     * @param DocumentBatch $documents Inputs.
     * @param ExecutionLimits $limits Budgets.
     * @return BatchResult
     * @since 0.1.0
     */
    public function execute(CompiledProgram $program, DocumentBatch $documents, ExecutionLimits $limits): BatchResult
    {
        $program->assertCompatible(Fixture::capabilities(), 'fixture.artifact.1', $limits);
        $documents->assertForProgram($program, $limits);
        $results = [];
        foreach ($documents->documents() as $document) {
            $results[] = new ExecutionResult($document->correlation, $document->contract, 'fixed-result');
        }
        return new BatchResult($documents, $results, $limits);
    }
}

Check::test('portable records and exact empty collection round trips', static function (): void {
    $limits = new ExecutionLimits();
    $contract = Fixture::contract();
    $source = Fixture::source();
    $capabilities = Fixture::capabilities();
    $plan = Fixture::plan();
    $input = Fixture::input();
    $compiled = Fixture::compiled();
    $finding = Fixture::finding();
    $path = new FindingPath(['0', 0, 'é', '']);
    $location = new SourceLocation('program', 'rule', 2);
    $requirement = new CompatibilityRequirement(1, '1.0.0', [], []);
    $pairs = [
        [$limits->toArray(), ExecutionLimits::fromArray($limits->toArray())->toArray()],
        [$contract->toArray(), ContractIdentity::fromArray($contract->toArray())->toArray()],
        [$source->toArray(), ProgramEnvelope::fromArray($source->toArray())->toArray()],
        [$capabilities->toArray(), CapabilitySet::fromArray($capabilities->toArray())->toArray()],
        [$plan->toArray(), PlanIdentity::fromArray($plan->toArray())->toArray()],
        [$input->toArray(), DocumentInput::fromArray($input->toArray())->toArray()],
        [$compiled->toArray(), CompiledProgram::fromArray($compiled->toArray(), $source, $limits)->toArray()],
        [$finding->toArray(), Finding::fromArray($finding->toArray())->toArray()],
        [$path->toArray(), FindingPath::fromArray($path->toArray())->toArray()],
        [$location->toArray(), SourceLocation::fromArray($location->toArray())->toArray()],
        [$requirement->toArray(), CompatibilityRequirement::fromArray($requirement->toArray())->toArray()],
    ];
    foreach ($pairs as [$a, $b]) {
        Check::that($a === $b);
    }
    Check::that($requirement->toArray()['features'] === []);
    $empty = new Finding('accepted', FindingSeverity::Info, new FindingPath([]), $location, 0);
    Check::that($empty->toArray()['parameters'] === []);
    Check::that(Finding::fromArray($empty->toArray())->parameters() === []);
    Check::that((new FindingPath(['0']))->toArray() !== (new FindingPath([0]))->toArray());
});

Check::test('closed versions, shapes, UTF8, identifiers, no arbitrary payload objects', static function (): void {
    foreach (['', "bad\0key", "bad\nkey", "bad\xFF", str_repeat('a', 129), ' leading'] as $bad) {
        Check::refuses(static fn () => new DocumentInput($bad, Fixture::contract(), ''));
    }
    foreach (['01.0.0', '1.0', 'dev-main', '1.0.0-beta', '1.0.0 ' ] as $version) {
        Check::refuses(static fn () => new ProgramEnvelope(Fixture::contract(), $version, ''));
    }
    $wire = Fixture::source()->toArray();
    Check::refuses(
        static fn () => ProgramEnvelope::fromArray([...$wire, 'wire_version' => 2]),
        RefusalCode::UnsupportedVersion
    );
    Check::refuses(static fn () => ProgramEnvelope::fromArray([...$wire, 'extra' => true]));
    Check::refuses(static fn () => ProgramEnvelope::fromArray([...$wire, 'payload' => new \stdClass()]));
    Check::refuses(static fn () => ProgramEnvelope::fromArray([...$wire, 'payload' => ['nested']]));
    Check::refuses(static fn () => Guard::object(array_fill_keys(range('a', 'z'), 'x') + array_fill(0, 10, 1)));
    Check::refuses(static fn () => new FindingPath(array_fill(0, 65, 'a')));
    Check::refuses(static fn () => new FindingPath([-1]));
});

Check::test('strict base64 and caller limits before decoding', static function (): void {
    $source = Fixture::source()->toArray();
    foreach (['YQ', 'YQ===', 'YQ==\n', 'YR==', '!!!!', '===='] as $payload) {
        Check::refuses(static fn () => ProgramEnvelope::fromArray([...$source, 'payload' => $payload]));
    }
    $limits = new ExecutionLimits(maxInputBytes: 1, maxOutputBytes: 1);
    Check::refuses(static fn () => ProgramEnvelope::fromArray($source, $limits), RefusalCode::ExhaustedLimit);
    Check::refuses(
        static fn () => DocumentInput::fromArray(Fixture::input()->toArray(), $limits),
        RefusalCode::ExhaustedLimit
    );
    Check::refuses(static fn () => Guard::unbase64(str_repeat('A', 8), 1), RefusalCode::ExhaustedLimit);
    Check::that(Guard::unbase64('', 0) === '');
    Check::that(Guard::unbase64('YQ==', 1) === 'a');
    Check::that(Guard::unbase64('YWI=', 2) === 'ab');
});

Check::test('finite budgets and signed64 integer contract', static function (): void {
    Check::that(PHP_INT_SIZE === 8);
    foreach (
        ['max_input_bytes', 'max_output_bytes', 'max_documents', 'max_findings', 'max_path_depth',
        'max_parameter_bytes', 'max_instructions', 'max_milliseconds'] as $key
    ) {
        foreach ([0, -1, PHP_INT_MAX] as $bad) {
            $data = (new ExecutionLimits())->toArray();
            $data[$key] = $bad;
            Check::refuses(static fn () => ExecutionLimits::fromArray($data));
        }
    }
    Check::that(Guard::encode(PHP_INT_MIN) === 'I20:-9223372036854775808');
    Check::that(Guard::encode(PHP_INT_MAX) === 'I19:9223372036854775807');
});

Check::test('independent metadata grammar and complete plan golden vectors', static function (): void {
    $bytes = file_get_contents(dirname(__DIR__) . '/resources/conformance/v1.json');
    if ($bytes === false) {
        throw new RuntimeException('Corpus unavailable.');
    }
    $corpus = Guard::object(json_decode($bytes, true, 32, JSON_THROW_ON_ERROR));
    foreach (Guard::items($corpus['metadata_vectors']) as $vector) {
        $vector = Guard::object($vector);
        Check::that(Guard::encode($vector['value']) === $vector['encoded']);
    }
    $plan = Guard::object($corpus['plan_vector']);
    Check::that(Fixture::plan()->toArray() === $plan['plan']);
    Check::that((new PlanCacheKey(Fixture::plan()))->value === $plan['cache_key']);
    Check::that(array_map(static fn (FindingSeverity $v): string => $v->value, FindingSeverity::cases())
        === $corpus['severity_tokens']);
    Check::that(array_map(static fn (RefusalCode $v): string => $v->value, RefusalCode::cases())
        === $corpus['refusal_tokens']);
    Check::that(Guard::encode(['ab', 'c']) !== Guard::encode(['a', 'bc']));
    Check::that(Guard::encode([1]) !== Guard::encode(['1']));
});

Check::test('every plan field changes cache identity', static function (): void {
    $original = Fixture::plan()->toArray();
    $originalKey = (new PlanCacheKey(Fixture::plan()))->value;
    foreach (
        ['generation' => 'generation.2', 'program_version' => '1.0.1',
        'source_digest' => str_repeat('f', 64), 'definition_digest' => str_repeat('f', 64),
        'schema_digest' => str_repeat('f', 64), 'options_digest' => str_repeat('f', 64)] as $field => $value
    ) {
        $changed = [...$original, $field => $value];
        Check::that((new PlanCacheKey(PlanIdentity::fromArray($changed)))->value !== $originalKey);
    }
    $caps = Fixture::capabilities()->toArray();
    foreach (
        ['engine_version' => '0.2.0', 'abi_major' => 2, 'api_version' => '2.0.0',
        'build_digest' => str_repeat('f', 64)] as $field => $value
    ) {
        $changed = [...$original, 'capabilities' => [...$caps, $field => $value]];
        Check::that((new PlanCacheKey(PlanIdentity::fromArray($changed)))->value !== $originalKey);
    }
});

Check::test('capability subset, exact corpus and tuple refusals', static function (): void {
    $observed = Fixture::capabilities();
    (new CompatibilityRequirement(1, '1.0.0', ['compile' => 1], [Fixture::contract()]))->assertSatisfiedBy($observed);
    Check::that(true);
    foreach (
        [new CompatibilityRequirement(2, '1.0.0', [], []),
        new CompatibilityRequirement(1, '1.0.1', [], []),
        new CompatibilityRequirement(1, '1.0.0', ['compile' => 2], []),
        new CompatibilityRequirement(1, '1.0.0', ['missing' => 1], [])] as $required
    ) {
        Check::refuses(static fn () => $required->assertSatisfiedBy($observed), RefusalCode::IncompatibleCapability);
    }
    $foreign = new ContractIdentity('example/transport', 'test-only', '0.1.0', str_repeat('f', 64));
    $required = new CompatibilityRequirement(1, '1.0.0', [], [$foreign]);
    Check::refuses(static fn () => $required->assertSatisfiedBy($observed), RefusalCode::IncompatibleCorpus);
    $a = new ContractIdentity('aa/b', 'p', '0.1.0', str_repeat('a', 64));
    $b = new ContractIdentity('a/z', 'p', '0.1.0', str_repeat('a', 64));
    $caps = new CapabilitySet('0.1.0', 1, '1.0.0', str_repeat('a', 64), [], [$a, $b]);
    Check::that($caps->contracts()[0]->owner === 'a/z');
    Check::refuses(static fn () => new CapabilitySet('0.1.0', 1, '1.0.0', str_repeat('a', 64), [], [$a, $a]));
});

Check::test('source agreement and exact artifact hydration tuple', static function (): void {
    $limits = new ExecutionLimits();
    $compiled = Fixture::compiled();
    $compiled->assertCompatible(Fixture::capabilities(), 'fixture.artifact.1', $limits);
    Check::that(true);
    Check::refuses(
        static fn () => new CompiledProgram(
            new ProgramEnvelope(Fixture::contract(), '1.0.0', 'changed'),
            Fixture::plan(),
            'fixture.artifact.1',
            '',
            $limits
        ),
        RefusalCode::InvalidProgram
    );
    Check::refuses(
        static fn () => $compiled->assertCompatible(Fixture::capabilities(), 'other', $limits),
        RefusalCode::InvalidProgram
    );
    $observed = CapabilitySet::fromArray([...Fixture::capabilities()->toArray(),
         'build_digest' => str_repeat(
             'f',
             64
         )]);
    Check::refuses(
        static fn () => $compiled->assertCompatible($observed, 'fixture.artifact.1', $limits),
        RefusalCode::IncompatibleCapability
    );
    $foreign = new ContractIdentity('example/foreign', 'p', '0.1.0', str_repeat('f', 64));
    $batch = new DocumentBatch([new DocumentInput('foreign', $foreign, '')], $limits);
    Check::refuses(static fn () => $batch->assertForProgram($compiled, $limits), RefusalCode::IncompatibleCorpus);
});

Check::test('batch correspondence, result identity and whole-batch port fake', static function (): void {
    $limits = new ExecutionLimits();
    $batch = new DocumentBatch([Fixture::input('a'), Fixture::input('b')], $limits);
    $port = new PortFake();
    $result = $port->execute($port->compile(Fixture::source(), Fixture::plan(), $limits), $batch, $limits);
    Check::that(count($result->results()) === 2);
    Check::that($result->toArray() === BatchResult::fromArray($result->toArray(), $batch, $limits)->toArray());
    Check::that($batch->toArray() === DocumentBatch::fromArray($batch->toArray(), $limits)->toArray());
    Check::refuses(static fn () => new BatchResult($batch, [$result->results()[0]], $limits));
    Check::refuses(static fn () => new BatchResult($batch, array_reverse($result->results()), $limits));
    $foreign = new ContractIdentity('example/foreign', 'p', '0.1.0', str_repeat('f', 64));
    Check::refuses(static fn () => new BatchResult($batch, [
        new ExecutionResult('a', $foreign, ''), $result->results()[1]], $limits));
    Check::refuses(static fn () => new DocumentBatch([Fixture::input('a'), Fixture::input('a')], $limits));
    Check::refuses(static fn () => new DocumentBatch([], $limits));
});

Check::test('finding order, typed paths and bounded machine parameters', static function (): void {
    $finding = Fixture::finding();
    $result = new ExecutionResult('a', Fixture::contract(), '', [$finding, Fixture::finding(2)]);
    Check::that($result->findings()[0]->severity === FindingSeverity::Error);
    Check::refuses(static fn () => new ExecutionResult('a', Fixture::contract(), '', [$finding, $finding]));
    Check::refuses(static fn () => new ExecutionResult('a', Fixture::contract(), '', [Fixture::finding(2), $finding]));
    foreach (
        [['bad' => ['nested']],
         ['bad' => new \stdClass()],
         ['bad' => 1.25],
         [0 => 'numeric-key']] as $parameters
    ) {
        Check::refuses(static fn () => Finding::fromArray([...$finding->toArray(), 'parameters' => $parameters]));
    }
    Check::refuses(static fn () => Finding::fromArray([...$finding->toArray(),
         'parameters' => ['bad' => str_repeat(
             'x',
             4097
         )]]));
    Check::refuses(
        static fn () => $finding->assertWithin(new ExecutionLimits(maxPathDepth: 1)),
        RefusalCode::ExhaustedLimit
    );
    Check::refuses(
        static fn () => $finding->assertWithin(new ExecutionLimits(maxParameterBytes: 1)),
        RefusalCode::ExhaustedLimit
    );
});

Check::test('aggregate budgets include metadata and preflight wire outputs', static function (): void {
    $limits = new ExecutionLimits();
    $batch = new DocumentBatch([Fixture::input()], $limits);
    Check::refuses(
        static fn () => $batch->assertWithin(new ExecutionLimits(maxInputBytes: 16)),
        RefusalCode::ExhaustedLimit
    );
    Check::refuses(
        static fn () => DocumentBatch::fromArray($batch->toArray(), new ExecutionLimits(maxInputBytes: 16)),
        RefusalCode::ExhaustedLimit
    );
    $result = new ExecutionResult('document.1', Fixture::contract(), '', [Fixture::finding()]);
    $low = new ExecutionLimits(maxOutputBytes: $result->byteSize() - 1);
    Check::refuses(static fn () => new BatchResult($batch, [$result], $low), RefusalCode::ExhaustedLimit);
    Check::refuses(static fn () => ExecutionResult::fromArray($result->toArray(), $low), RefusalCode::ExhaustedLimit);
    $wire = (new BatchResult($batch, [$result], $limits))->toArray();
    Check::refuses(static fn () => BatchResult::fromArray($wire, $batch, $low), RefusalCode::ExhaustedLimit);
});

Check::test('immutable snapshots detach external PHP references', static function (): void {
    $version = 1;
    $features = ['compile' => &$version];
    $contract = Fixture::contract();
    $contracts = [&$contract];
    $caps = new CapabilitySet('0.1.0', 1, '1.0.0', str_repeat('b', 64), $features, $contracts);
    $version = 2;
    $contract = new ContractIdentity('example/other', 'p', '0.1.0', str_repeat('b', 64));
    Check::that($caps->features()['compile'] === 1 && $caps->contracts()[0]->owner === 'example/transport');
    $segment = 'original';
    $path = new FindingPath([&$segment]);
    $segment = 'changed';
    Check::that($path->segments() === ['original']);
    $value = 'original';
    $finding = new Finding(
        'code',
        FindingSeverity::Info,
        $path,
        new SourceLocation('program', 'rule', 0),
        0,
        ['value' => &$value]
    );
    $value = 'changed';
    Check::that($finding->parameters() === ['value' => 'original']);
    $input = Fixture::input();
    $batch = new DocumentBatch([&$input], new ExecutionLimits());
    $input = Fixture::input('changed');
    Check::that($batch->documents()[0]->correlation === 'document.1');
});

Check::test('deterministic property cases preserve binary payload and typed identity', static function (): void {
    for ($index = 0; $index < 128; $index++) {
        $bytes = chr($index) . "\0\xFF" . str_repeat('x', $index);
        $program = new ProgramEnvelope(Fixture::contract(), '1.0.0', $bytes);
        Check::that(ProgramEnvelope::fromArray($program->toArray())->bytes === $bytes);
        $path = new FindingPath([(string) $index, $index]);
        Check::that(FindingPath::fromArray($path->toArray())->segments() === [(string) $index, $index]);
    }
});

echo sprintf("%d tests / %d assertions passed.\n", Check::$tests, Check::$assertions);
