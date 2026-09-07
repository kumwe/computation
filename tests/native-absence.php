<?php

declare(strict_types=1);

namespace Kumwe\Computation\Tests;

use Kumwe\Computation\CapabilitySet;
use Kumwe\Computation\ConfigProvider;
use Kumwe\Computation\ExecutionRefused;
use Kumwe\Computation\Internal\NativeRuntime;
use Kumwe\Computation\NativeCompatibility;
use Kumwe\Computation\RefusalCode;
use RuntimeException;

if (in_array('--list-json', $argv ?? [], true)) {
    echo json_encode([
        'native absence and no-autoload readiness' => 'tests/native-absence.php',
    ], JSON_THROW_ON_ERROR) . "\n";
    exit(0);
}

require $argv[1] ?? dirname(__DIR__) . '/vendor/autoload.php';

if (extension_loaded('kumwe_engine')) {
    throw new RuntimeException('Run the native absence gate without the extension loaded.');
}
$nativeAutoloads = 0;
spl_autoload_register(static function (string $class) use (&$nativeAutoloads): void {
    if (str_starts_with(strtolower($class), 'kumwe\\engine\\')) {
        ++$nativeAutoloads;
        throw new RuntimeException('Native names must never invoke a Composer fallback.');
    }
});
$compatibility = new NativeCompatibility(
    new CapabilitySet('0.0.0', 1, '0.0.0', str_repeat('a', 64), [], []),
    '0.0.0-dev',
    str_repeat('b', 40),
    str_repeat('c', 64),
);
$refused = false;
try {
    NativeRuntime::create($compatibility);
} catch (ExecutionRefused $failure) {
    $refused = $failure->reason === RefusalCode::IncompatibleCapability;
}
if (!$refused || $nativeAutoloads !== 0) {
    throw new RuntimeException('Missing native extension did not fail closed without autoload.');
}
$provider = (new ConfigProvider())();
if (!isset($provider['dependencies'])) {
    throw new RuntimeException('The explicit provider must describe services without probing native classes.');
}
echo "Native absence and no-autoload readiness checks passed.\n";
