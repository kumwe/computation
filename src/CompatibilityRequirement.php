<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Required exact ABI/API and feature/profile subset; unknown versions never downgrade.
 * @since 0.1.0
 */
final readonly class CompatibilityRequirement
{
    /**
 * @var array<string,int> Exact feature versions required.
 * @since 0.1.0
 */
    private array $features;
    /**
 * @var list<ContractIdentity> Required profiles.
 * @since 0.1.0
 */
    private array $contracts;

    /**
 * @param int $abiMajor Required ABI major.
 * @param string $apiVersion Exact API version.
 * @param array<string,int> $features Exact required feature versions.
 * @param list<ContractIdentity> $contracts Required semantic identities.
 * @since 0.1.0
 */
    public function __construct(public int $abiMajor, public string $apiVersion, array $features, array $contracts)
    {
        Guard::integer($abiMajor, 1, 65535);
        Guard::version($apiVersion);
        $this->features = Guard::features($features);
        $this->contracts = Guard::contracts($contracts);
    }

    /**
 * @param CapabilitySet $observed Observed handshake.
 * @return void
 * @throws ExecutionRefused On mismatch.
 * @since 0.1.0
 */
    public function assertSatisfiedBy(CapabilitySet $observed): void
    {
        Guard::require(
            $this->abiMajor === $observed->abiMajor && $this->apiVersion === $observed->apiVersion,
            RefusalCode::IncompatibleCapability
        );
        foreach ($this->features as $name => $version) {
            Guard::require(($observed->features()[$name] ?? null) === $version, RefusalCode::IncompatibleCapability);
        }
        foreach ($this->contracts as $contract) {
            Guard::require($observed->supports($contract), RefusalCode::IncompatibleCorpus);
        }
    }

    /**
 * @return array<string,mixed> Required tuple in fixed field order.
 * @since 0.1.0
 */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'abi_major' => $this->abiMajor, 'api_version' => $this->apiVersion,
            'features' => Guard::featureRecords($this->features), 'contracts' => array_map(
                static fn (ContractIdentity $contract): array => $contract->toArray(),
                $this->contracts
            )];
    }

    /**
 * @param array<string,mixed> $data Wire requirement.
 * @return self Validated requirement.
 * @since 0.1.0
 */
    public static function fromArray(array $data): self
    {
        Guard::shape($data, ['wire_version', 'abi_major', 'api_version', 'features', 'contracts']);
        return new self(
            Guard::integer($data['abi_major'], 1, 65535),
            Guard::version($data['api_version']),
            Guard::featuresFromWire($data['features']),
            Guard::contractsFromWire($data['contracts'])
        );
    }
}
