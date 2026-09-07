<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Host-selected exact candidate tuple; construction never asserts that a release was verified.
 * @since 0.2.0
 */
final readonly class NativeCompatibility
{
    /**
     * @param CapabilitySet $capabilities Exact Engine feature, build and corpus tuple.
     * @param string $extensionVersion Exact extension version, including a candidate suffix when applicable.
     * @param string $embeddedEngineCommit Exact source commit embedded by the extension build.
     * @param string $embeddedSourceSha256 Exact Engine source archive digest embedded by the extension build.
     * @since 0.2.0
     */
    public function __construct(
        public CapabilitySet $capabilities,
        public string $extensionVersion,
        public string $embeddedEngineCommit,
        public string $embeddedSourceSha256,
    ) {
        Guard::token($extensionVersion);
        Guard::require(preg_match('/^[0-9a-f]{40}$/D', $embeddedEngineCommit) === 1);
        Guard::digest($embeddedSourceSha256);
    }

    /**
     * Verify the observed build without invoking autoload, downloading code, or selecting a fallback.
     * @param array<string,mixed> $observed Native handshake; unknown extra informational fields are permitted.
     * @return void
     * @throws ExecutionRefused On any tuple mismatch.
     * @since 0.2.0
     */
    public function assertObserved(array $observed): void
    {
        Guard::require(
            ($observed['extension_version'] ?? null) === $this->extensionVersion
            && ($observed['embedded_engine_commit'] ?? null) === $this->embeddedEngineCommit
            && ($observed['embedded_source_sha256'] ?? null) === $this->embeddedSourceSha256,
            RefusalCode::IncompatibleCapability,
        );
        $native = CapabilitySet::fromArray(Guard::object($observed['computation'] ?? null));
        Guard::require($native->key() === $this->capabilities->key(), RefusalCode::IncompatibleCapability);
    }
}
