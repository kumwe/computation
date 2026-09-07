<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Successful complete document result; business error findings remain result data.
 * @since 0.1.0
 */
final readonly class ExecutionResult
{
    /**
     * @var list<Finding> Validated strictly increasing declaration order.
     * @since 0.1.0
     */
    private array $findings;

    /**
     * @param string $correlation Input correlation identity.
     * @param ContractIdentity $contract Exact result semantic identity matching input.
     * @param string $bytes Opaque output bytes.
     * @param list<Finding> $findings Ordered machine findings.
     * @since 0.1.0
     */
    public function __construct(
        public string $correlation,
        public ContractIdentity $contract,
        public string $bytes,
        array $findings = [],
    ) {
        Guard::token($correlation);
        Guard::bytes($bytes);
        Guard::require(array_is_list($findings) && count($findings) <= 65536);
        $copy = [];
        $last = -1;
        $total = strlen($bytes);
        foreach ($findings as $finding) {
            if (!$finding instanceof Finding) {
                throw new ExecutionRefused(RefusalCode::InvalidInput);
            }
            Guard::require($finding->ordinal > $last);
            $last = $finding->ordinal;
            $total += $finding->byteSize();
            Guard::require($total <= 67108864, RefusalCode::ExhaustedLimit);
            $copy[] = $finding;
        }
        $this->findings = $copy;
    }

    /**
     * @return list<Finding> Stable immutable order.
     * @since 0.1.0
     */
    public function findings(): array
    {
        return $this->findings;
    }

    /**
     * @return int Output and all result/finding metadata bytes.
     * @since 0.1.0
     */
    public function byteSize(): int
    {
        $bytes = strlen($this->bytes) + strlen($this->correlation) + strlen(Guard::encode($this->contract->toArray()));
        foreach ($this->findings as $finding) {
            $bytes += $finding->byteSize();
        }
        return $bytes;
    }

    /**
     * @return array<string,mixed> Complete versioned result.
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'correlation' => $this->correlation, 'contract' => $this->contract->toArray(),
            'payload' => base64_encode($this->bytes), 'findings' => array_map(
                static fn (Finding $finding): array => $finding->toArray(),
                $this->findings
            )];
    }

    /**
     * @param array<string,mixed> $data Complete wire result.
     * @return self Validated result.
     * @since 0.1.0
     * @param ?ExecutionLimits $limits Caller budgets before decoding; defaults remain bounded.
     */
    public static function fromArray(array $data, ?ExecutionLimits $limits = null): self
    {
        Guard::shape($data, ['wire_version', 'correlation', 'contract', 'payload', 'findings']);
        $limits ??= new ExecutionLimits();
        $bytes = Guard::base64Size($data['payload'], min(16777216, $limits->maxOutputBytes));
        $bytes += strlen(Guard::token($data['correlation']))
            + strlen(Guard::encode(ContractIdentity::fromArray(Guard::object($data['contract']))->toArray()));
        $findings = [];
        foreach (Guard::items($data['findings'], min(65536, $limits->maxFindings)) as $finding) {
            $finding = Finding::fromArray(Guard::object($finding));
            $finding->assertWithin($limits);
            $bytes += $finding->byteSize();
            Guard::require($bytes <= $limits->maxOutputBytes, RefusalCode::ExhaustedLimit);
            $findings[] = $finding;
        }
        Guard::require($bytes <= $limits->maxOutputBytes, RefusalCode::ExhaustedLimit);
        return new self(
            Guard::token($data['correlation']),
            ContractIdentity::fromArray(Guard::object($data['contract'])),
            Guard::unbase64($data['payload'], min(16777216, $limits->maxOutputBytes)),
            $findings
        );
    }
}
