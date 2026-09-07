<?php

/**
 * Enforce the package boundary without external tooling.
 *
 * Every source file declares strict types, sits in the canonical PSR-4 namespace, declares exactly the type
 * its file names, and names only reviewed native, canonical port and container interfaces. No host or
 * framework edge is admitted. Native availability is checked only by the explicit internal guard, which
 * disables autoload and cannot choose a PHP fallback. Composer metadata preserves the
 * repository license.
 *
 * @since 0.1.0
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$source = $root . '/src';
$namespaceRoot = 'Kumwe\\Computation';
$layers = [
    '' => [
        'ContractIdentity', 'CapabilitySet', 'CompatibilityRequirement', 'ExecutionLimits', 'ProgramEnvelope',
        'PlanIdentity', 'PlanCacheKey', 'CompiledProgram', 'DocumentInput', 'DocumentBatch', 'FindingPath',
        'FindingSeverity', 'SourceLocation', 'Finding', 'ExecutionResult', 'BatchResult', 'RefusalCode',
        'ExecutionRefused', 'Compiler', 'Executor', 'Internal', 'NativeAdapter', 'NativeAdapterFactory',
        'NativeCanonicalEncoder', 'NativeCanonicalEncoderFactory', 'NativeCompatibility', 'ConfigProvider',
    ],
    'Internal' => [
        'ContractIdentity', 'CapabilitySet', 'CompatibilityRequirement', 'ExecutionLimits', 'ProgramEnvelope',
        'PlanIdentity', 'PlanCacheKey', 'CompiledProgram', 'DocumentInput', 'DocumentBatch', 'FindingPath',
        'FindingSeverity', 'SourceLocation', 'Finding', 'ExecutionResult', 'BatchResult', 'RefusalCode',
        'ExecutionRefused', 'Compiler', 'Executor', 'Internal', 'NativeAdapter', 'NativeAdapterFactory',
        'NativeCanonicalEncoder', 'NativeCanonicalEncoderFactory', 'NativeCompatibility', 'ConfigProvider',
    ],
];
$nativeImports = [
    'src/ConfigProvider.php' => ['Kumwe\\CanonicalJson\\CanonicalEncoder'],
    'src/NativeAdapter.php' => ['Kumwe\\Engine\\Runtime', 'Kumwe\\Engine\\Exception\\BindingFailure'],
    'src/NativeAdapterFactory.php' => ['Psr\\Container\\ContainerInterface'],
    'src/NativeCanonicalEncoder.php' => [
        'Kumwe\\CanonicalJson\\CanonicalEncoder', 'Kumwe\\CanonicalJson\\Limits', 'Kumwe\\CanonicalJson\\FindingCode',
        'Kumwe\\Engine\\Runtime', 'Kumwe\\Engine\\Exception\\BindingFailure',
    ],
    'src/NativeCanonicalEncoderFactory.php' => ['Psr\\Container\\ContainerInterface', 'Kumwe\\CanonicalJson\\Limits'],
    'src/Internal/NativeRuntime.php' => ['Kumwe\\Engine\\Runtime', 'Kumwe\\Engine\\Exception\\BindingFailure'],
];
$runtimeSelection = ['class_alias', 'class_exists', 'interface_exists', 'extension_loaded', 'function_exists'];
$errors = [];
$files = [];

if (is_dir($source)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
    );
    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}
sort($files);
if ($files === []) {
    $errors[] = 'The source tree must not be empty.';
}

foreach ($files as $path) {
    $relative = str_replace('\\', '/', substr($path, strlen($root) + 1));
    $code = file_get_contents($path);
    if (!is_string($code)) {
        $errors[] = $relative . ' cannot be read.';
        continue;
    }
    if (!str_contains($code, 'declare(strict_types=1);')) {
        $errors[] = $relative . ' does not enable strict types.';
    }

    $pathPart = substr($relative, strlen('src/'), -strlen('.php'));
    $segments = explode('/', $pathPart);
    $fileName = array_pop($segments);
    $expectedNamespace = $namespaceRoot . ($segments === [] ? '' : '\\' . implode('\\', $segments));
    if (
        preg_match('/^namespace\s+([^;]+);/m', $code, $namespaceMatch) !== 1
        || trim($namespaceMatch[1]) !== $expectedNamespace
    ) {
        $errors[] = $relative . ' does not declare its PSR-4 namespace ' . $expectedNamespace . '.';
        continue;
    }
    $declared = preg_match_all(
        '/^(?:final\s+|abstract\s+|readonly\s+)*(?:class|interface|enum|trait)\s+(\w+)/m',
        $code,
        $typeMatches,
    );
    if ($declared !== 1 || $typeMatches[1][0] !== $fileName) {
        $errors[] = $relative . ' must declare exactly the one type named by its file.';
    }

    $layer = $segments[0] ?? '';
    if ($layer === '' && !in_array($fileName, $layers[''], true)) {
        $errors[] = $relative . ' declares an unreviewed public type.';
    }
    if ($layer === 'Internal' && !in_array($fileName, ['Guard', 'NativeRuntime'], true)) {
        $errors[] = $relative . ' declares an unreviewed internal type.';
    }
    if ($layer === 'Internal' && preg_match('/(?:^|\s)@internal\b/', $code) !== 1) {
        $errors[] = $relative . ' must document its internal-only status.';
    }
    if (!isset($layers[$layer])) {
        $errors[] = $relative . ' belongs to an unclassified layer; every source file sits under a known layer.';
        continue;
    }

    if ($relative === 'src/Internal/NativeRuntime.php') {
        $normalized = preg_replace('/\s+/', '', $code);
        if (!is_string($normalized) || !str_contains($normalized, "class_exists(Runtime::class,false)")) {
            $errors[] = 'Native presence checks must suppress autoload explicitly.';
        }
        if (is_string($normalized) && substr_count($normalized, 'class_exists(') !== 1) {
            $errors[] = 'Only one reviewed native presence check is permitted.';
        }
    }
    $nativeAdapter = isset($nativeImports[$relative]);
    foreach (token_get_all($code) as $token) {
        if (!is_array($token)) {
            continue;
        }
        [$id, $text, $line] = $token;
        if (
            $nativeAdapter && in_array($id, [T_STRING, T_NAME_FULLY_QUALIFIED], true)
            && in_array(strtolower(ltrim($text, '\\')), [
                'json_encode', 'json_decode', 'serialize', 'unserialize', 'sort', 'ksort', 'usort', 'uksort',
                'asort', 'uasort', 'eval', 'bcadd', 'bcsub', 'bcmul', 'bcdiv',
            ], true)
        ) {
            $errors[] = sprintf('%s:%d adds PHP semantic processing through %s.', $relative, $line, $text);
        }
        if (
            in_array($id, [T_STRING, T_NAME_FULLY_QUALIFIED], true)
            && in_array(strtolower(ltrim($text, '\\')), $runtimeSelection, true)
        ) {
            $guard = $relative === 'src/Internal/NativeRuntime.php'
                && in_array(strtolower(ltrim($text, '\\')), ['extension_loaded', 'class_exists'], true);
            if (!$guard) {
                $errors[] = sprintf('%s:%d selects behaviour at runtime through %s().', $relative, $line, $text);
            }
            continue;
        }
        if (!in_array($id, [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
            continue;
        }
        $name = ltrim($text, '\\');
        if (!str_contains($name, '\\')) {
            continue;
        }
        if ($name === $namespaceRoot) {
            continue;
        }
        if (in_array($name, $nativeImports[$relative] ?? [], true)) {
            continue;
        }
        if (!str_starts_with($name, $namespaceRoot . '\\')) {
            $errors[] = sprintf(
                '%s:%d names %s, which is neither a package type nor a PHP type.',
                $relative,
                $line,
                $name,
            );
            continue;
        }
        $target = explode('\\', substr($name, strlen($namespaceRoot) + 1))[0];
        if (!in_array($target, $layers[$layer], true)) {
            $errors[] = sprintf('%s crosses from %s into forbidden %s.', $relative, $layer, $target);
        }
    }
}

$composerBytes = file_get_contents($root . '/composer.json');
$composer = is_string($composerBytes) ? json_decode($composerBytes, true) : null;
$composer = is_array($composer) ? $composer : [];
if (($composer['name'] ?? null) !== 'kumwe/computation') {
    $errors[] = 'composer.json must name the package kumwe/computation.';
}
if (($composer['license'] ?? null) !== 'Apache-2.0') {
    $errors[] = 'composer.json must advertise the repository Apache-2.0 license exactly.';
}
$runtime = is_array($composer['require'] ?? null) ? array_keys($composer['require']) : [];
if ($runtime !== ['php', 'php-64bit', 'ext-kumwe_engine', 'kumwe/canonical-json', 'psr/container']) {
    $errors[] = 'composer.json must declare exactly the reviewed native adapter dependencies.';
}
if (($composer['autoload'] ?? null) !== ['psr-4' => [$namespaceRoot . '\\' => 'src/']]) {
    $errors[] = 'composer.json must autoload exactly the one canonical namespace Kumwe\\Computation\\ from src/.';
}
if (array_key_exists('version', $composer)) {
    $errors[] = 'composer.json must not carry a version; the changelog is the release record.';
}

if ($errors !== []) {
    fwrite(STDERR, "Architecture verification failed:\n - " . implode("\n - ", $errors) . "\n");
    exit(1);
}

echo 'Architecture verified: ' . count($files)
    . " source files under Kumwe\\Computation, only reviewed native and PSR container edges.\n";
