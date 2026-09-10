<?php

/**
 * Validate recorded native dependency evidence without claiming this package's own release verification.
 * @since 0.3.1
 */

declare(strict_types=1);

/**
 * @param mixed $value Decoded JSON object.
 * @return array<string, mixed> Validated map.
 * @since 0.3.1
 */
function nativeReleaseObject(mixed $value): array
{
    if (!is_array($value) || array_is_list($value)) {
        throw new RuntimeException('Native release evidence requires an object.');
    }
    $result = [];
    foreach ($value as $key => $member) {
        if (!is_string($key)) {
            throw new RuntimeException('Native release evidence requires string keys.');
        }
        $result[$key] = $member;
    }
    return $result;
}

/**
 * @param bool $condition Required evidence invariant.
 * @param string $message Refusal diagnostic.
 * @return void
 * @since 0.3.1
 */
function nativeReleaseRequire(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/**
 * @param mixed $value Candidate SHA-256.
 * @return bool Whether an exact digest is recorded.
 * @since 0.3.1
 */
function nativeReleaseDigest(mixed $value): bool
{
    return is_string($value) && preg_match('/^[0-9a-f]{64}$/D', $value) === 1;
}

/**
 * @param mixed $value Candidate Git source identity.
 * @return bool Whether an exact source commit is recorded.
 * @since 0.3.1
 */
function nativeReleaseCommit(mixed $value): bool
{
    return is_string($value) && preg_match('/^[0-9a-f]{40}$/D', $value) === 1;
}

/**
 * @param array<string, mixed> $record Verified upstream release evidence reference.
 * @param string $package Exact owner.
 * @param bool $published Whether to validate publisher provenance without claiming independent verification.
 * @return void
 * @since 0.3.1
 */
function nativeReleaseCoordinate(array $record, string $package, bool $published = false): void
{
    nativeReleaseRequire(($record['package'] ?? null) === $package, 'Wrong native dependency owner.');
    nativeReleaseRequire(
        ($record['state'] ?? null) === ($published ? 'package-released' : 'release-verified'),
        'Upstream release evidence state differs.',
    );
    $version = $record['version'] ?? null;
    if (
        !is_string($version) || $version === '0.0.0'
        || preg_match('/^(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)$/D', $version) !== 1
    ) {
        throw new RuntimeException('An exact stable upstream version is required.');
    }
    nativeReleaseRequire(($record['tag'] ?? null) === 'v' . $version, 'Upstream version tag differs.');
    nativeReleaseRequire(nativeReleaseCommit($record['commit'] ?? null), 'Upstream source commit is missing.');
    nativeReleaseRequire(nativeReleaseDigest($record['archive_sha256'] ?? null), 'Upstream archive digest is missing.');
    $attestation = nativeReleaseObject($record[$published ? 'provenance' : 'attestation'] ?? null);
    $uri = $attestation['uri'] ?? null;
    nativeReleaseRequire(
        is_string($uri) && filter_var($uri, FILTER_VALIDATE_URL) !== false
        && str_starts_with($uri, 'https://') && !str_contains($uri, '#'),
        'Upstream release evidence needs an external HTTPS URI.',
    );
    nativeReleaseRequire(nativeReleaseDigest($attestation['sha256'] ?? null), 'Release evidence digest is missing.');
    if ($published) {
        $release = 'https://github.com/' . $package . '/releases/';
        nativeReleaseRequire(
            ($record['release_uri'] ?? null) === $release . 'tag/v' . $version
            && $uri === $release . 'download/v' . $version . '/build-provenance.sigstore.json',
            'Published native evidence must identify its exact owner release and OIDC provenance.',
        );
    }
}

/**
 * @param array<string, mixed> $adapter Native adapter requirement and source evidence.
 * @param array<string, mixed> $baseline Separately verified portable release evidence.
 * @param array<string, mixed> $composer Actual Composer runtime requirements.
 * @return void
 * @since 0.3.1
 */
function nativeReleaseVerify(array $adapter, array $baseline, array $composer): void
{
    nativeReleaseRequire(($adapter['schema'] ?? null) === 'kumwe-native-adapter/v1', 'Wrong native adapter schema.');
    $require = nativeReleaseObject($composer['require'] ?? null);
    $source = nativeReleaseObject($adapter['binding_source'] ?? null);
    nativeReleaseRequire(($source['repository'] ?? null) === 'kumwe/kumwe-engine', 'Wrong binding source repository.');
    nativeReleaseRequire(nativeReleaseCommit($source['commit'] ?? null), 'An exact binding source is required.');
    nativeReleaseRequire(
        ($adapter['composer_constraint'] ?? null) === ($require['ext-kumwe_engine'] ?? null),
        'Composer and native adapter extension versions differ.',
    );
    nativeReleaseRequire(
        ($adapter['verification'] ?? null) === 'required-actual-extension-and-independent-exact-tuple',
        'Actual extension and independent complete tuple verification remain mandatory.',
    );
    nativeReleaseRequire(
        ($adapter['binding_features'] ?? null) === ['opaque-compiled-results/1'],
        'Opaque result admission remains mandatory.',
    );
    $status = $adapter['release_status'] ?? null;
    if ($status === 'candidate-not-release-verified') {
        nativeReleaseRequire(($adapter['composer_constraint'] ?? null) === '0.0.0-dev', 'Candidate version differs.');
        nativeReleaseRequire(
            ($adapter['release_dependencies'] ?? null) === null,
            'Candidate cannot claim verified releases.',
        );
        return;
    }
    $published = $status === 'published-native-dependencies';
    nativeReleaseRequire(
        $published || $status === 'verified-native-dependencies',
        'Unknown native dependency evidence state.',
    );
    $dependencies = nativeReleaseObject($adapter['release_dependencies'] ?? null);
    nativeReleaseRequire(
        array_keys($dependencies) === ['portable_contracts', 'engine', 'binding'],
        'The ordered portable, Engine and binding release prerequisites must all be recorded.',
    );
    $portable = nativeReleaseObject($dependencies['portable_contracts']);
    $engine = nativeReleaseObject($dependencies['engine']);
    $binding = nativeReleaseObject($dependencies['binding']);
    nativeReleaseCoordinate($portable, 'kumwe/computation');
    nativeReleaseCoordinate($engine, 'kumwe/engine', $published);
    nativeReleaseCoordinate($binding, 'kumwe/kumwe-engine', $published);
    nativeReleaseRequire(
        ($baseline['status'] ?? null) === 'release-verified'
        && ($baseline['verified_release'] ?? null) === $portable
        && ($baseline['verified_attestation'] ?? null) === ($portable['attestation'] ?? null)
        && ($baseline['native_adapter_is_baseline'] ?? null) === false,
        'The separate portable release baseline must match the native admission record.',
    );
    nativeReleaseRequire(
        ($portable['require'] ?? null) === ['php' => '^8.5', 'php-64bit' => '^8.5']
        && ($portable['native_bindings_present'] ?? null) === false,
        'The pre-Engine baseline must be extension-free and have no native bindings.',
    );
    foreach (['api_digest', 'capability_digest'] as $field) {
        nativeReleaseRequire(nativeReleaseDigest($portable[$field] ?? null), 'Portable manifest digest is missing.');
    }
    $corpora = nativeReleaseObject($portable['corpus_digests'] ?? null);
    nativeReleaseRequire(
        array_keys($corpora) === ['resources/conformance/v1.json']
        && nativeReleaseDigest($corpora['resources/conformance/v1.json']),
        'The exact portable transport corpus is required.',
    );
    nativeReleaseRequire(
        ($engine['abi_frozen'] ?? null) === true && ($engine['abi_major'] ?? null) === 1
        && ($engine['abi_minor'] ?? null) === 0,
        'The selected Engine requires frozen ABI 1.0.',
    );
    nativeReleaseRequire(
        nativeReleaseDigest($engine['embedded_source_sha256'] ?? null),
        'Engine tar identity is missing.',
    );
    nativeReleaseRequire(nativeReleaseDigest($binding['api_digest'] ?? null), 'Binding API digest is missing.');
    nativeReleaseRequire(
        ($binding['embedded_engine_commit'] ?? null) === $engine['commit']
        && $binding['version'] === $engine['version']
        && ($binding['embedded_source_sha256'] ?? null) === $engine['embedded_source_sha256'],
        'Binding does not embed the selected independently verified Engine source.',
    );
    nativeReleaseRequire(
        $source['commit'] === $binding['commit']
        && ($adapter['composer_constraint'] ?? null) === $binding['version']
        && ($binding['extension_version'] ?? null) === $binding['version'],
        'CI binding source and exact Composer extension requirement must select the verified release.',
    );
}

/**
 * Preserve the published portable surface byte for byte while qualifying the native successor.
 * @param array<string, mixed> $baseline Recorded baseline and exact source preservation map.
 * @param string $root Current package source root.
 * @return void
 * @since 0.3.1
 */
function nativeReleaseVerifyPortableSources(array $baseline, string $root): void
{
    if (($baseline['status'] ?? null) !== 'release-verified') {
        return;
    }
    $portable = nativeReleaseObject($baseline['verified_release'] ?? null);
    nativeReleaseCoordinate($portable, 'kumwe/computation');
    $preservation = nativeReleaseObject($baseline['portable_source_preservation'] ?? null);
    $files = nativeReleaseObject($preservation['files'] ?? null);
    $paths = ['resources/conformance/v1.json', 'src/Internal/Guard.php'];
    foreach (
        [
        'ContractIdentity', 'CapabilitySet', 'CompatibilityRequirement', 'ExecutionLimits', 'ProgramEnvelope',
        'PlanIdentity', 'PlanCacheKey', 'CompiledProgram', 'DocumentInput', 'DocumentBatch', 'FindingPath',
        'FindingSeverity', 'SourceLocation', 'Finding', 'ExecutionResult', 'BatchResult', 'RefusalCode',
        'ExecutionRefused', 'Compiler', 'Executor',
        ] as $name
    ) {
        $paths[] = 'src/' . $name . '.php';
    }
    sort($paths, SORT_STRING);
    nativeReleaseRequire(array_keys($files) === $paths, 'All 22 portable source/corpus identities must be preserved.');
    foreach ($files as $path => $digest) {
        nativeReleaseRequire(
            nativeReleaseDigest($digest) && is_file($root . '/' . $path)
            && hash_file('sha256', $root . '/' . $path) === $digest,
            'Portable source differs from its verified baseline: ' . $path,
        );
    }
    $corpora = nativeReleaseObject($portable['corpus_digests'] ?? null);
    nativeReleaseRequire(
        ($corpora['resources/conformance/v1.json'] ?? null) === $files['resources/conformance/v1.json'],
        'The preserved transport corpus must equal the independently verified portable release corpus.',
    );
}
