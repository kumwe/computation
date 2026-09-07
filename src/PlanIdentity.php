<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Complete cache and compilation identity; trusted generation is supplied by the host.
 * @since 0.1.0
 */
final readonly class PlanIdentity
{
    /**
 * Construct a complete validated value; does not verify an upstream release.
 * @param ContractIdentity $contract contract.
 * @param string $programVersion programVersion.
 * @param string $sourceDigest sourceDigest.
 * @param string $generation generation.
 * @param string $definitionDigest definitionDigest.
 * @param string $schemaDigest schemaDigest.
 * @param string $optionsDigest optionsDigest.
 * @param CapabilitySet $capabilities capabilities.
 * @throws ExecutionRefused If a value violates this boundary.
 * @since 0.1.0
 */
    public function __construct(
        public ContractIdentity $contract,
        public string $programVersion,
        public string $sourceDigest,
        public string $generation,
        public string $definitionDigest,
        public string $schemaDigest,
        public string $optionsDigest,
        public CapabilitySet $capabilities,
    ) {
        Guard::version($programVersion);
        Guard::digest($sourceDigest);
        Guard::token($generation);
        Guard::digest($definitionDigest);
        Guard::digest($schemaDigest);
        Guard::digest($optionsDigest);
        Guard::require($capabilities->supports($contract), RefusalCode::IncompatibleCorpus);
    }

    /**
 * @return array<string,mixed> Versioned portable record in prescribed field order.
 * @since 0.1.0
 */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'contract' => $this->contract->toArray(), 'program_version' => $this->programVersion, 'source_digest' => $this->sourceDigest, 'generation' => $this->generation, 'definition_digest' => $this->definitionDigest, 'schema_digest' => $this->schemaDigest, 'options_digest' => $this->optionsDigest, 'capabilities' => $this->capabilities->toArray()];
    }

    /**
 * @param array<string,mixed> $data Exact wire record.
 * @return self Validated value.
 * @since 0.1.0
 */
    public static function fromArray(array $data): self
    {
        Guard::shape($data, ['wire_version', 'contract', 'program_version', 'source_digest', 'generation', 'definition_digest', 'schema_digest', 'options_digest', 'capabilities']);
        return new self(ContractIdentity::fromArray(Guard::object($data['contract'])), Guard::version($data['program_version']), Guard::digest($data['source_digest']), Guard::token($data['generation']), Guard::digest($data['definition_digest']), Guard::digest($data['schema_digest']), Guard::digest($data['options_digest']), CapabilitySet::fromArray(Guard::object($data['capabilities'])));
    }
}
