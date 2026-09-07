<?php

declare(strict_types=1);

use Kumwe\Computation\CapabilitySet;
use Kumwe\Computation\CompatibilityRequirement;
use Kumwe\Computation\ContractIdentity;
use Kumwe\Computation\DocumentBatch;
use Kumwe\Computation\DocumentInput;
use Kumwe\Computation\ExecutionLimits;
use Kumwe\Computation\PlanCacheKey;
use Kumwe\Computation\PlanIdentity;
use Kumwe\Computation\ProgramEnvelope;

$autoload = $argv[1] ?? dirname(__DIR__) . '/vendor/autoload.php';
require $autoload;

// Synthetic transport fixture only: these coordinates do not claim a released semantic/native profile.
$contract = new ContractIdentity('example/transport', 'test-only', '0.1.0', str_repeat('a', 64));
$capabilities = new CapabilitySet('0.1.0', 1, '1.0.0', str_repeat('b', 64), ['compile' => 1], [$contract]);
(new CompatibilityRequirement(1, '1.0.0', ['compile' => 1], [$contract]))->assertSatisfiedBy($capabilities);
$limits = new ExecutionLimits();
$source = new ProgramEnvelope($contract, '1.0.0', 'opaque-source');
$plan = new PlanIdentity($contract, '1.0.0', $source->digest(), 'generation.1', str_repeat('c', 64),
    str_repeat('d', 64), str_repeat('e', 64), $capabilities);
$source->assertPlan($plan, $limits);
$batch = new DocumentBatch([new DocumentInput('document.1', $contract, 'already-normalized')], $limits);
$roundTrip = DocumentBatch::fromArray($batch->toArray(), $limits);
if ($roundTrip->toArray() !== $batch->toArray() || strlen((new PlanCacheKey($plan))->value) !== 64) {
    throw new RuntimeException('The portable boundary failed its round trip.');
}
echo "Portable program/plan/batch contract verified; no native execution was requested.\n";
