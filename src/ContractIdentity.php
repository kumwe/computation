<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Supplied exact semantic coordinate; no semantic implementation or release verification.
 * @since 0.1.0
 */
final readonly class ContractIdentity
{
    /**
 * Construct a complete validated value; does not verify an upstream release.
 * @param string $owner owner.
 * @param string $profile profile.
 * @param string $version version.
 * @param string $corpusDigest corpusDigest.
 * @throws ExecutionRefused If a value violates this boundary.
 * @since 0.1.0
 */
    public function __construct(
        public string $owner,
        public string $profile,
        public string $version,
        public string $corpusDigest,
    ) {
        Guard::token($owner);
        Guard::require(preg_match('/^[a-z0-9][a-z0-9_.-]*\/[a-z0-9][a-z0-9_.-]*$/D', $owner) === 1);
        Guard::token($profile);
        Guard::version($version);
        Guard::digest($corpusDigest);
    }

    /**
 * @return array<string,mixed> Versioned portable record in prescribed field order.
 * @since 0.1.0
 */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'owner' => $this->owner, 'profile' => $this->profile, 'version' => $this->version, 'corpus_digest' => $this->corpusDigest];
    }

    /**
 * @param array<string,mixed> $data Exact wire record.
 * @return self Validated value.
 * @since 0.1.0
 */
    public static function fromArray(array $data): self
    {
        Guard::shape($data, ['wire_version', 'owner', 'profile', 'version', 'corpus_digest']);
        return new self(Guard::token($data['owner']), Guard::token($data['profile']), Guard::version($data['version']), Guard::digest($data['corpus_digest']));
    }

    /**
 * @return string Unambiguous exact coordinate key.
 * @since 0.1.0
 */
    public function key(): string
    {
        return Guard::encode($this->toArray());
    }
}
