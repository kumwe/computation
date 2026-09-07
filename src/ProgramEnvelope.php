<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Opaque complete semantic program; compilation input never evaluates an AST.
 * @since 0.1.0
 */
final readonly class ProgramEnvelope
{
    /**
 * Construct a complete validated value; does not verify an upstream release.
 * @param ContractIdentity $contract contract.
 * @param string $programVersion programVersion.
 * @param string $bytes bytes.
 * @throws ExecutionRefused If a value violates this boundary.
 * @since 0.1.0
 */
    public function __construct(
        public ContractIdentity $contract,
        public string $programVersion,
        public string $bytes,
    ) {
        Guard::version($programVersion);
        Guard::bytes($bytes);
    }

    /**
 * @return array<string,mixed> Versioned portable record in prescribed field order.
 * @since 0.1.0
 */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'contract' => $this->contract->toArray(), 'program_version' => $this->programVersion, 'payload' => base64_encode($this->bytes)];
    }

    /**
 * @param array<string,mixed> $data Exact wire record.
 * @return self Validated value.
 * @since 0.1.0
 * @param ?ExecutionLimits $limits Caller budgets before decoding; defaults remain bounded.
 */
    public static function fromArray(array $data, ?ExecutionLimits $limits = null): self
    {
        Guard::shape($data, ['wire_version', 'contract', 'program_version', 'payload']);
        return new self(ContractIdentity::fromArray(Guard::object($data['contract'])), Guard::version($data['program_version']), Guard::unbase64($data['payload'], min(16777216, ($limits ?? new ExecutionLimits())->maxInputBytes)));
    }

    /**
 * @return string SHA-256 of unchanged source bytes.
 * @since 0.1.0
 */
    public function digest(): string
    {
        return hash('sha256', $this->bytes);
    }

    /**
 * @param PlanIdentity $plan Intended plan.
 * @param ExecutionLimits $limits Caller budgets.
 * @return void
 * @since 0.1.0
 */
    public function assertPlan(PlanIdentity $plan, ExecutionLimits $limits): void
    {
        Guard::require(
            $this->contract->key() === $plan->contract->key()
            && $this->programVersion === $plan->programVersion && $this->digest() === $plan->sourceDigest,
            RefusalCode::InvalidProgram
        );
        Guard::require(strlen($this->bytes) <= $limits->maxInputBytes, RefusalCode::ExhaustedLimit);
    }
}
