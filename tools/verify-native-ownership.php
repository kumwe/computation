<?php

/**
 * Verify portable/native ownership and the transport-only corpus; exercise fail-closed negative cases.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

/**
 * Require an object-shaped array without coercing malformed keys.
 * @param mixed $value Candidate JSON object.
 * @return array<string, mixed> Validated object.
 * @since 0.1.0
 */
function ownershipObject(mixed $value): array
{
    if (!is_array($value) || array_is_list($value)) {
        throw new RuntimeException('Expected a nonempty JSON object.');
    }
    $result = [];
    foreach ($value as $key => $member) {
        if (!is_string($key)) {
            throw new RuntimeException('Expected string object keys.');
        }
        $result[$key] = $member;
    }

    return $result;
}

/**
 * Require a list without accepting a map or scalar.
 * @param mixed $value Candidate JSON list.
 * @return list<mixed> Validated list.
 * @since 0.1.0
 */
function ownershipList(mixed $value): array
{
    if (!is_array($value) || !array_is_list($value)) {
        throw new RuntimeException('Expected a JSON list.');
    }

    return $value;
}

/**
 * Refuse a violated ownership invariant.
 * @param bool $condition Accepted state.
 * @param string $message Diagnostic independent of payload contents.
 * @return void
 * @since 0.1.0
 */
function ownershipRequire(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * Parse a required JSON document.
 * @param string $path Document path.
 * @return array<string, mixed> Parsed object.
 * @since 0.1.0
 */
function ownershipRead(string $path): array
{
    $bytes = file_get_contents($path);
    if (!is_string($bytes)) {
        throw new RuntimeException('Required ownership input is missing.');
    }

    return ownershipObject(json_decode($bytes, true, 512, JSON_THROW_ON_ERROR));
}

/**
 * Check exact ownership, public coverage and baseline-only native/corpus declarations.
 * @param array<string, mixed> $record Ownership document.
 * @param array<string, mixed> $api Public API document.
 * @param array<string, mixed> $corpus Transport corpus.
 * @return void
 * @since 0.1.0
 */
function ownershipVerify(array $record, array $api, array $corpus): void
{
    ownershipRequire(($record['schema'] ?? null) === 'kumwe-computation-native-ownership/v1', 'Wrong schema.');
    ownershipRequire(($record['status'] ?? null) === 'reviewed-draft', 'Ownership is a reviewed draft.');
    ownershipRequire(($record['phase'] ?? null) === 'contract_baseline', 'Wrong contract phase.');
    ownershipRequire(($record['native_implementation'] ?? null) === false, 'No native implementation is present.');
    ownershipRequire(($record['semantic_dependencies'] ?? null) === [], 'No semantic dependency is selected.');
    ownershipRequire(($record['corpus'] ?? null) === 'resources/conformance/v1.json', 'Wrong transport corpus path.');
    ownershipRequire(($api['package'] ?? null) === 'kumwe/computation', 'Wrong public API owner.');
    $symbols = ownershipObject($api['symbols'] ?? null);
    ownershipRequire(count($symbols) === 20, 'The reviewed baseline has exactly 20 public types.');
    $portable = ownershipList($record['php'] ?? null);
    ownershipRequire(count($portable) === 20, 'Missing or excess portable declarations.');
    $seen = [];
    $declared = [];
    foreach ($portable as $entry) {
        $type = ownershipObject($entry);
        $name = $type['fqcn'] ?? null;
        if (!is_string($name) || !array_key_exists($name, $symbols)) {
            throw new RuntimeException('Portable declaration is absent from the exact public API.');
        }
        ownershipRequire(str_starts_with($name, 'Kumwe\\Computation\\'), 'Wrong portable namespace.');
        ownershipRequire(($type['owner'] ?? null) === 'kumwe/computation', 'Wrong portable owner.');
        ownershipRequire(($type['runtime'] ?? null) === 'composer', 'Wrong portable runtime.');
        ownershipRequire(($type['api_manifest'] ?? null) === 'resources/public-api/v1.json', 'Wrong API path.');
        $key = strtolower($name);
        ownershipRequire(!isset($seen[$key]), 'Duplicate case-insensitive FQCN.');
        $seen[$key] = true;
        $declared[$name] = true;
    }
    ownershipRequire(array_diff_key($symbols, $declared) === [], 'Public API has an unowned type.');
    $expectedNative = [
        'Kumwe\\Engine\\Runtime' => ['capabilities(): array', 'compile(array): array', 'execute(array): array'],
        'Kumwe\\Engine\\Exception\\BindingFailure' => [],
    ];
    $native = ownershipList($record['native'] ?? null);
    ownershipRequire(count($native) === count($expectedNative), 'Missing or excess native reservation.');
    $nativeSeen = [];
    foreach ($native as $entry) {
        $type = ownershipObject($entry);
        $name = $type['fqcn'] ?? null;
        if (!is_string($name) || !array_key_exists($name, $expectedNative)) {
            throw new RuntimeException('Unexpected native FQCN.');
        }
        ownershipRequire(($type['owner'] ?? null) === 'kumwe/kumwe-engine', 'Wrong native owner.');
        ownershipRequire(($type['runtime'] ?? null) === 'zend-reserved', 'Native declaration is reserved only.');
        ownershipRequire(($type['methods'] ?? null) === $expectedNative[$name], 'Native methods differ from review.');
        $key = strtolower($name);
        ownershipRequire(!isset($seen[$key]), 'Portable/native FQCN collision.');
        $seen[$key] = true;
        $nativeSeen[$name] = true;
    }
    ownershipRequire(array_diff_key($expectedNative, $nativeSeen) === [], 'Missing native reservation.');
    $abi = ownershipObject($record['c_abi_proposal'] ?? null);
    ownershipRequire(($abi['owner'] ?? null) === 'kumwe/engine', 'Wrong C ABI owner.');
    ownershipRequire(($abi['frozen'] ?? null) === false, 'C ABI remains a non-frozen proposal.');
    ownershipRequire(($abi['prefix'] ?? null) === 'kumwe_engine_v1_', 'Wrong C ABI prefix.');
    ownershipRequire(
        ($abi['operations'] ?? null) === ['capabilities', 'compile', 'execute', 'buffer_view', 'buffer_release'],
        'Wrong C ABI operation set.',
    );
    $refusals = [
        'invalid_input', 'unsupported_version', 'incompatible_capability', 'incompatible_corpus',
        'invalid_program', 'exhausted_limit', 'cancelled', 'internal_failure',
    ];
    $statuses = ['success' => 0];
    foreach ($refusals as $index => $name) {
        $statuses[$name] = $index + 1;
    }
    ownershipRequire(($abi['statuses'] ?? null) === $statuses, 'Expected success zero and eight refusal codes.');
    ownershipRequire(($corpus['schema'] ?? null) === 'kumwe-computation-transport-corpus/v1', 'Wrong corpus schema.');
    ownershipRequire(($corpus['profile'] ?? null) === 'transport-only-v1', 'Wrong transport profile.');
    ownershipRequire(($corpus['native_implementation'] ?? null) === false, 'No native corpus implementation.');
    ownershipRequire(($corpus['semantic_implementations'] ?? null) === [], 'Corpus must not claim semantic parity.');
    ownershipRequire(
        ($corpus['scope'] ?? null)
            === 'Closed metadata identity, bounds and portable transport only; no native or domain algorithm parity.',
        'Corpus scope must remain transport-only.',
    );
    ownershipRequire(($corpus['severity_tokens'] ?? null) === ['info', 'warning', 'error'], 'Wrong severity tokens.');
    ownershipRequire(($corpus['refusal_tokens'] ?? null) === $refusals, 'Wrong refusal tokens.');
    ownershipRequire(ownershipList($corpus['metadata_vectors'] ?? null) !== [], 'Metadata vectors are missing.');
    ownershipObject($corpus['plan_vector'] ?? null);
}

/**
 * Prove a malformed candidate is rejected by the actual gate.
 * @param string $label Failure-mode label.
 * @param array<string, mixed> $record Ownership candidate.
 * @param array<string, mixed> $api API candidate.
 * @param array<string, mixed> $corpus Corpus candidate.
 * @return void
 * @since 0.1.0
 */
function ownershipRejects(string $label, array $record, array $api, array $corpus): void
{
    try {
        ownershipVerify($record, $api, $corpus);
    } catch (RuntimeException) {
        return;
    }
    throw new RuntimeException('Negative ownership fixture was accepted: ' . $label);
}

try {
    $root = dirname(__DIR__);
    $record = ownershipRead($root . '/resources/native-ownership/v1.json');
    $api = ownershipRead($root . '/resources/public-api/v1.json');
    $corpusPath = $root . '/resources/conformance/v1.json';
    $corpus = ownershipRead($corpusPath);
    ownershipVerify($record, $api, $corpus);
    $digest = hash_file('sha256', $corpusPath);
    $documentation = file_get_contents($root . '/docs/conformance.md');
    ownershipRequire(is_string($digest) && is_string($documentation), 'Corpus evidence is missing.');
    if (!is_string($digest) || !is_string($documentation)) {
        throw new RuntimeException('Corpus evidence is unreadable.');
    }
    ownershipRequire(str_contains($documentation, 'SHA-256: `' . $digest . '`'), 'Corpus digest drifted.');
    $capabilities = ownershipRead($root . '/resources/capabilities/v1.json');
    ownershipRequire(array_key_exists('native_requirements', $capabilities), 'Native requirement decision missing.');
    ownershipRequire($capabilities['native_requirements'] === null, 'Baseline must not claim native requirements.');
    $linked = false;
    foreach (ownershipList($capabilities['capabilities'] ?? null) as $entry) {
        $capability = ownershipObject($entry);
        if (in_array('docs/conformance.md', ownershipList($capability['documentation'] ?? null), true)) {
            $linked = true;
        }
    }
    ownershipRequire($linked, 'Capabilities must link the corpus digest documentation.');
    /** @var list<string> $arguments */
    $arguments = $_SERVER['argv'] ?? [];
    $options = array_slice($arguments, 1);
    ownershipRequire($options === [] || $options === ['--self-test'], 'Use --self-test or no options.');
    if ($options === ['--self-test']) {
        ownershipRejects('missing record', [], $api, $corpus);
        $broken = $record;
        $broken['php'] = 'invalid';
        ownershipRejects('malformed list', $broken, $api, $corpus);
        $portable = ownershipList($record['php'] ?? null);
        ownershipRequire(count($portable) === 20, 'Self-test needs the reviewed input.');
        $first = ownershipObject($portable[0]);
        $broken = $record;
        $broken['php'] = array_slice($portable, 1);
        ownershipRejects('missing public type', $broken, $api, $corpus);
        $duplicate = $portable;
        $duplicate[1] = $first;
        $broken['php'] = $duplicate;
        ownershipRejects('duplicate public type', $broken, $api, $corpus);
        $first['fqcn'] = 'kumwe\\computation\\ContractIdentity';
        $duplicate[1] = $first;
        $broken['php'] = $duplicate;
        ownershipRejects('case-folded collision', $broken, $api, $corpus);
        $first = ownershipObject($portable[0]);
        $first['owner'] = 'kumwe/kumwe-engine';
        $portable[0] = $first;
        $broken['php'] = $portable;
        ownershipRejects('cross-owner public type', $broken, $api, $corpus);
        $native = ownershipList($record['native'] ?? null);
        $firstNative = ownershipObject($native[0]);
        $firstNative['fqcn'] = 'Kumwe\\Computation\\Compiler';
        $native[0] = $firstNative;
        $broken = $record;
        $broken['native'] = $native;
        ownershipRejects('native/public collision', $broken, $api, $corpus);
        $broken = $record;
        $broken['native'] = [];
        ownershipRejects('missing native owner', $broken, $api, $corpus);
        $broken = $record;
        $broken['native_implementation'] = true;
        ownershipRejects('claimed native implementation', $broken, $api, $corpus);
        $broken = $record;
        $broken['semantic_dependencies'] = ['kumwe/conversion'];
        ownershipRejects('selected semantic dependency', $broken, $api, $corpus);
        $abi = ownershipObject($record['c_abi_proposal'] ?? null);
        $abi['frozen'] = true;
        $broken = $record;
        $broken['c_abi_proposal'] = $abi;
        ownershipRejects('frozen ABI claim', $broken, $api, $corpus);
        $abi = ownershipObject($record['c_abi_proposal'] ?? null);
        $abi['statuses'] = ['success' => 1];
        $broken['c_abi_proposal'] = $abi;
        ownershipRejects('invalid status table', $broken, $api, $corpus);
        $brokenCorpus = $corpus;
        $brokenCorpus['native_implementation'] = true;
        ownershipRejects('native corpus claim', $record, $api, $brokenCorpus);
        $brokenCorpus = $corpus;
        $brokenCorpus['semantic_implementations'] = ['decimal'];
        ownershipRejects('semantic corpus claim', $record, $api, $brokenCorpus);
        echo "Ownership self-test passed: 14 malformed/missing/duplicate/ownership/claim cases.\n";
    }
    echo "Ownership verified: 20 portable types, two native reservations, transport-only corpus; no native claim.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Ownership verification failed: ' . $error->getMessage() . "\n");
    exit(1);
}
