<?php

declare(strict_types=1);

namespace Kumwe\Computation;

/**
 * Explicit service map; the host supplies NativeCompatibility and a request-scoped container lifetime.
 * @since 0.2.0
 */
final class ConfigProvider
{
    /**
     * @return array<string,mixed> Factories and interface aliases, with no automatic readiness or native probing.
     * @since 0.2.0
     */
    public function __invoke(): array
    {
        return ['dependencies' => [
            'factories' => [
                NativeAdapter::class => NativeAdapterFactory::class,
                NativeCanonicalEncoder::class => NativeCanonicalEncoderFactory::class,
            ],
            'aliases' => [
                Compiler::class => NativeAdapter::class,
                Executor::class => NativeAdapter::class,
            ],
            'shared' => [NativeAdapter::class => true, NativeCanonicalEncoder::class => true],
        ]];
    }
}
