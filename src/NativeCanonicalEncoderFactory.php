<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\CanonicalJson\Limits;
use Kumwe\Computation\Internal\NativeRuntime;
use Psr\Container\ContainerInterface;

/**
 * Explicit canonical adapter composition with exact native compatibility and canonical budget metadata.
 * @since 0.2.0
 */
final class NativeCanonicalEncoderFactory
{
    /**
     * @param ContainerInterface $container Request-scoped container supplying NativeCompatibility and optional Limits.
     * @return NativeCanonicalEncoder Fail-closed native canonical service.
     * @throws ExecutionRefused When configuration or native compatibility is unavailable.
     * @since 0.2.0
     */
    public function __invoke(ContainerInterface $container): NativeCanonicalEncoder
    {
        $compatibility = $container->get(NativeCompatibility::class);
        $limits = $container->has(Limits::class) ? $container->get(Limits::class) : new Limits();
        if (!$compatibility instanceof NativeCompatibility || !$limits instanceof Limits) {
            throw new ExecutionRefused(RefusalCode::IncompatibleCapability);
        }
        return new NativeCanonicalEncoder(NativeRuntime::create($compatibility), $compatibility, $limits);
    }
}
