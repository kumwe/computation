<?php

/**
 * Gate native dependency admission and prove that partial or invented evidence cannot qualify a successor.
 * @since 0.3.1
 */

declare(strict_types=1);

require_once __DIR__ . '/lib/native-release.php';

/**
 * @param string $path Local reviewed JSON document.
 * @return array<string, mixed> Parsed object.
 * @since 0.3.1
 */
function nativeReleaseRead(string $path): array
{
    $bytes = file_get_contents($path);
    if (!is_string($bytes)) {
        throw new RuntimeException('Native release input is missing.');
    }
    return nativeReleaseObject(json_decode($bytes, true, 512, JSON_THROW_ON_ERROR));
}

/**
 * @param array<string, mixed> $adapter Mutated synthetic adapter fixture.
 * @param array<string, mixed> $baseline Mutated synthetic baseline fixture.
 * @param array<string, mixed> $composer Mutated synthetic requirement fixture.
 * @return void
 * @since 0.3.1
 */
function nativeReleaseRejects(array $adapter, array $baseline, array $composer): void
{
    try {
        nativeReleaseVerify($adapter, $baseline, $composer);
    } catch (RuntimeException) {
        return;
    }
    throw new RuntimeException('An incomplete native admission fixture was accepted.');
}

/**
 * Exercise synthetic evidence only; these identities never enter the shipped release records.
 * @return void
 * @since 0.3.1
 */
function nativeReleaseSelfTest(): void
{
    $coordinate = static fn (string $package): array => [
        'package' => $package, 'state' => 'release-verified', 'version' => '1.0.0', 'tag' => 'v1.0.0',
        'commit' => str_repeat('a', 40), 'archive_sha256' => str_repeat('b', 64),
        'attestation' => ['uri' => 'https://example.invalid/independent-fixture.yaml', 'sha256' => str_repeat('c', 64)],
    ];
    $portable = $coordinate('kumwe/computation') + [
        'require' => ['php' => '^8.5', 'php-64bit' => '^8.5'], 'native_bindings_present' => false,
        'api_digest' => str_repeat('d', 64), 'capability_digest' => str_repeat('e', 64),
        'corpus_digests' => ['resources/conformance/v1.json' => str_repeat('f', 64)],
    ];
    $engine = $coordinate('kumwe/engine') + [
        'abi_major' => 1, 'abi_minor' => 0, 'abi_frozen' => true, 'embedded_source_sha256' => str_repeat('d', 64),
    ];
    $binding = $coordinate('kumwe/kumwe-engine') + [
        'api_digest' => str_repeat('e', 64), 'embedded_engine_commit' => $engine['commit'],
        'embedded_source_sha256' => $engine['embedded_source_sha256'], 'extension_version' => '1.0.0',
    ];
    $dependencies = ['portable_contracts' => $portable, 'engine' => $engine, 'binding' => $binding];
    $adapter = [
        'schema' => 'kumwe-native-adapter/v1',
        'binding_source' => ['repository' => 'kumwe/kumwe-engine', 'commit' => $binding['commit']],
        'composer_constraint' => '1.0.0', 'release_status' => 'verified-native-dependencies',
        'verification' => 'required-actual-extension-and-independent-exact-tuple',
        'binding_features' => ['opaque-compiled-results/1'], 'release_dependencies' => $dependencies,
    ];
    $baseline = [
        'status' => 'release-verified', 'verified_release' => $portable,
        'verified_attestation' => $portable['attestation'], 'native_adapter_is_baseline' => false,
    ];
    $composer = ['require' => ['ext-kumwe_engine' => '1.0.0']];
    nativeReleaseVerify($adapter, $baseline, $composer);
    $count = 0;
    foreach (array_keys($dependencies) as $name) {
        foreach (['package', 'state', 'version', 'tag', 'commit', 'archive_sha256', 'attestation'] as $field) {
            $broken = $adapter;
            $changed = $dependencies;
            $entry = $changed[$name];
            $entry[$field] = null;
            $changed[$name] = $entry;
            $broken['release_dependencies'] = $changed;
            nativeReleaseRejects($broken, $baseline, $composer);
            ++$count;
        }
    }
    foreach (
        [
        ['portable_contracts', 'native_bindings_present', true],
        ['portable_contracts', 'require', ['php' => '^8.5', 'php-64bit' => '^8.5', 'ext-kumwe_engine' => '1.0.0']],
        ['portable_contracts', 'api_digest', null], ['portable_contracts', 'capability_digest', null],
        ['portable_contracts', 'corpus_digests', []], ['engine', 'abi_frozen', false], ['engine', 'abi_major', 2],
        ['engine', 'embedded_source_sha256', null], ['binding', 'embedded_engine_commit', str_repeat('b', 40)],
        ['binding', 'embedded_source_sha256', str_repeat('f', 64)], ['binding', 'extension_version', '0.0.0-dev'],
        ['binding', 'api_digest', null],
        ] as [$name, $field, $value]
    ) {
        $broken = $adapter;
        $changed = $dependencies;
        $entry = $changed[$name];
        $entry[$field] = $value;
        $changed[$name] = $entry;
        $broken['release_dependencies'] = $changed;
        nativeReleaseRejects($broken, $baseline, $composer);
        ++$count;
    }
    foreach (['status', 'verified_release', 'verified_attestation', 'native_adapter_is_baseline'] as $field) {
        $broken = $baseline;
        $broken[$field] = null;
        nativeReleaseRejects($adapter, $broken, $composer);
        ++$count;
    }
    $broken = $adapter;
    $broken['binding_source'] = ['repository' => 'kumwe/kumwe-engine', 'commit' => str_repeat('f', 40)];
    nativeReleaseRejects($broken, $baseline, $composer);
    nativeReleaseRejects($adapter, $baseline, ['require' => ['ext-kumwe_engine' => '^1.0']]);
    $broken = $adapter;
    $broken['release_status'] = 'candidate-not-release-verified';
    nativeReleaseRejects($broken, $baseline, $composer);
    $count += 3;
    echo 'Native release self-test passed: ' . $count . " incomplete/different evidence cases refused.\n";
}

try {
    $root = dirname(__DIR__);
    $adapter = nativeReleaseRead($root . '/resources/native-adapter.json');
    nativeReleaseVerifyPortableSources(nativeReleaseRead($root . '/resources/contract-baseline/v1.json'), $root);
    nativeReleaseVerify(
        $adapter,
        nativeReleaseRead($root . '/resources/contract-baseline/v1.json'),
        nativeReleaseRead($root . '/composer.json'),
    );
    /** @var list<string> $arguments */
    $arguments = $_SERVER['argv'] ?? [];
    $options = array_slice($arguments, 1);
    if ($options === ['--github-outputs']) {
        $source = nativeReleaseObject($adapter['binding_source'] ?? null);
        $stable = ($adapter['release_status'] ?? null) === 'verified-native-dependencies';
        $dependencies = $stable ? nativeReleaseObject($adapter['release_dependencies'] ?? null) : [];
        $binding = $stable ? nativeReleaseObject($dependencies['binding'] ?? null) : [];
        $outputs = [
            'commit' => $source['commit'] ?? '', 'stable' => $stable ? 'true' : 'false',
            'tag' => $binding['tag'] ?? '', 'archive_sha256' => $binding['archive_sha256'] ?? '',
        ];
        $path = getenv('GITHUB_OUTPUT');
        if (!is_string($path) || $path === '') {
            throw new RuntimeException('GitHub output path is required.');
        }
        foreach ($outputs as $key => $value) {
            if (!is_string($value) || str_contains($value, "\n") || str_contains($value, "\r")) {
                throw new RuntimeException('Invalid native source workflow output.');
            }
            if (file_put_contents($path, $key . '=' . $value . "\n", FILE_APPEND) === false) {
                throw new RuntimeException('Cannot record validated native source workflow output.');
            }
        }
    } elseif ($options === ['--self-test']) {
        nativeReleaseSelfTest();
    } elseif ($options !== []) {
        throw new RuntimeException('Use --github-outputs, --self-test or no options.');
    }
    echo "Native dependency source/evidence state verified; independent runtime tuple remains required.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Native release verification failed: ' . $error->getMessage() . "\n");
    exit(1);
}
