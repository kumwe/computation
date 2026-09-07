<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Explicit observed capabilities; construction does not probe a native runtime.
 * @since 0.1.0
 */
final readonly class CapabilitySet
{
    /**
 * @var array<string,int> Detached sorted feature versions.
 * @since 0.1.0
 */
    private array $features;
    /**
 * @var list<ContractIdentity> Sorted exact supported contracts.
 * @since 0.1.0
 */
    private array $contracts;

    /**
 * @param string $engineVersion Exact observed engine version.
 * @param int $abiMajor ABI major.
 * @param string $apiVersion Exact API version.
 * @param string $buildDigest Exact build identity.
 * @param array<string,int> $features Available feature versions.
 * @param list<ContractIdentity> $contracts Exact supported profiles/corpora.
 * @since 0.1.0
 */
    public function __construct(
        public string $engineVersion,
        public int $abiMajor,
        public string $apiVersion,
        public string $buildDigest,
        array $features,
        array $contracts,
    ) {
        Guard::version($engineVersion);
        Guard::integer($abiMajor, 1, 65535);
        Guard::version($apiVersion);
        Guard::digest($buildDigest);
        $this->features = Guard::features($features);
        $this->contracts = Guard::contracts($contracts);
    }

    /**
 * @return array<string,int> Immutable feature snapshot.
 * @since 0.1.0
 */
    public function features(): array
    {
        return $this->features;
    }

    /**
 * @return list<ContractIdentity> Exact supported profiles.
 * @since 0.1.0
 */
    public function contracts(): array
    {
        return $this->contracts;
    }

    /**
 * @param ContractIdentity $contract Exact required profile.
 * @return bool Whether advertised.
 * @since 0.1.0
 */
    public function supports(ContractIdentity $contract): bool
    {
        foreach ($this->contracts as $supported) {
            if ($supported->key() === $contract->key()) {
                return true;
            }
        }
        return false;
    }

    /**
 * @return array<string,mixed> Ordered versioned metadata.
 * @since 0.1.0
 */
    public function toArray(): array
    {
        $features = Guard::featureRecords($this->features);
        return ['wire_version' => 1, 'engine_version' => $this->engineVersion, 'abi_major' => $this->abiMajor,
            'api_version' => $this->apiVersion, 'build_digest' => $this->buildDigest, 'features' => $features,
            'contracts' => array_map(static fn (ContractIdentity $c): array => $c->toArray(), $this->contracts)];
    }

    /**
 * @param array<string,mixed> $data Wire record.
 * @return self Validated observation.
 * @since 0.1.0
 */
    public static function fromArray(array $data): self
    {
        Guard::shape($data, ['wire_version', 'engine_version', 'abi_major', 'api_version', 'build_digest',
            'features', 'contracts']);
        $features = Guard::featuresFromWire($data['features']);
        $contracts = Guard::contractsFromWire($data['contracts']);
        return new self(
            Guard::version($data['engine_version']),
            Guard::integer($data['abi_major'], 1, 65535),
            Guard::version($data['api_version']),
            Guard::digest($data['build_digest']),
            $features,
            $contracts
        );
    }

    /**
 * @return string Complete exact tuple identity.
 * @since 0.1.0
 */
    public function key(): string
    {
        return Guard::encode($this->toArray());
    }
}
