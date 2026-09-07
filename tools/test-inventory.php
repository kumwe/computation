<?php

/**
 * Discover the actual portable and native entrypoints without replacing their required execution gates.
 * @since 0.2.0
 */

declare(strict_types=1);

$inventory = [];
foreach (['tests/run.php', 'tests/native.php', 'tests/native-absence.php'] as $entrypoint) {
    $lines = [];
    $status = 1;
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($entrypoint) . ' --list-json', $lines, $status);
    if ($status !== 0) {
        throw new RuntimeException('Actual test entrypoint discovery failed.');
    }
    $entries = json_decode(implode("\n", $lines), true, 64, JSON_THROW_ON_ERROR);
    if (!is_array($entries) || $entries === []) {
        throw new RuntimeException('Actual test entrypoint discovered no tests.');
    }
    foreach ($entries as $name => $path) {
        if (!is_string($name) || !is_string($path) || isset($inventory[$name])) {
            throw new RuntimeException('Test discovery returned malformed or duplicate evidence.');
        }
        $inventory[$name] = $path;
    }
}
echo json_encode($inventory, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
