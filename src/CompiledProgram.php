<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Opaque Engine artifact with exact tuple and source agreement; not native pointer storage.
 * @since 0.1.0
 */
final readonly class CompiledProgram
{
    /**
     * @param ProgramEnvelope $source Original opaque program, required for identity validation.
     * @param PlanIdentity $plan Complete immutable compilation identity.
     * @param string $format Exact Engine-owned artifact-format token.
     * @param string $bytes Opaque compiled artifact; never interpreted here.
     * @param ExecutionLimits $limits Compile input and output budgets.
     * @since 0.1.0
     */
    public function __construct(
        ProgramEnvelope $source,
        public PlanIdentity $plan,
        public string $format,
        public string $bytes,
        ExecutionLimits $limits,
    ) {
        $source->assertPlan($plan, $limits);
        Guard::token($format);
        Guard::bytes($bytes);
        Guard::require(strlen($bytes) <= $limits->maxOutputBytes, RefusalCode::ExhaustedLimit);
    }

    /**
     * Validate immediately before hydration; the Engine still validates artifact contents.
     * @param CapabilitySet $observed Current exact native handshake.
     * @param string $format Accepted artifact format.
     * @param ExecutionLimits $limits Caller input budget.
     * @return void
     * @throws ExecutionRefused On incompatibility or excessive input.
     * @since 0.1.0
     */
    public function assertCompatible(CapabilitySet $observed, string $format, ExecutionLimits $limits): void
    {
        Guard::token($format);
        Guard::require($this->plan->capabilities->key() === $observed->key(), RefusalCode::IncompatibleCapability);
        Guard::require($this->format === $format, RefusalCode::InvalidProgram);
        Guard::require(strlen($this->bytes) <= $limits->maxInputBytes, RefusalCode::ExhaustedLimit);
    }

    /**
     * @return array<string,mixed> Versioned metadata with opaque base64 artifact.
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'plan' => $this->plan->toArray(), 'format' => $this->format,
            'payload' => base64_encode($this->bytes)];
    }

    /**
     * @param array<string,mixed> $data Serialized artifact.
     * @param ProgramEnvelope $source Original source for identity verification.
     * @param ExecutionLimits $limits Caller budgets.
     * @return self Validated descriptor; artifact semantics remain untrusted until native hydration.
     * @since 0.1.0
     */
    public static function fromArray(array $data, ProgramEnvelope $source, ExecutionLimits $limits): self
    {
        Guard::shape($data, ['wire_version', 'plan', 'format', 'payload']);
        return new self(
            $source,
            PlanIdentity::fromArray(Guard::object($data['plan'])),
            Guard::token($data['format']),
            Guard::unbase64($data['payload'], min(16777216, $limits->maxOutputBytes)),
            $limits
        );
    }
}
