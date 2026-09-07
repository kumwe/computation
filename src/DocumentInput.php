<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Already normalized opaque document with explicit exact semantic identity.
 * @since 0.1.0
 */
final readonly class DocumentInput
{
    /**
 * Construct a complete validated value; does not verify an upstream release.
 * @param string $correlation correlation.
 * @param ContractIdentity $contract contract.
 * @param string $bytes bytes.
 * @throws ExecutionRefused If a value violates this boundary.
 * @since 0.1.0
 */
    public function __construct(
        public string $correlation,
        public ContractIdentity $contract,
        public string $bytes,
    ) {
        Guard::token($correlation);
        Guard::bytes($bytes);
    }

    /**
 * @return array<string,mixed> Versioned portable record in prescribed field order.
 * @since 0.1.0
 */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'correlation' => $this->correlation, 'contract' => $this->contract->toArray(), 'payload' => base64_encode($this->bytes)];
    }

    /**
 * @param array<string,mixed> $data Exact wire record.
 * @return self Validated value.
 * @since 0.1.0
 * @param ?ExecutionLimits $limits Caller budgets before decoding; defaults remain bounded.
 */
    public static function fromArray(array $data, ?ExecutionLimits $limits = null): self
    {
        Guard::shape($data, ['wire_version', 'correlation', 'contract', 'payload']);
        return new self(Guard::token($data['correlation']), ContractIdentity::fromArray(Guard::object($data['contract'])), Guard::unbase64($data['payload'], min(16777216, ($limits ?? new ExecutionLimits())->maxInputBytes)));
    }
}
