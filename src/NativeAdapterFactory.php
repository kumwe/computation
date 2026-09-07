<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\NativeRuntime;
use Psr\Container\ContainerInterface;

/**
 * Explicit construction from a host-provided immutable native compatibility tuple.
 * @since 0.2.0
 */
final class NativeAdapterFactory
{
    /**
     * @param ContainerInterface $container Request-scoped service container.
     * @return NativeAdapter One shared compiler/executor instance within that request scope.
     * @throws ExecutionRefused When configuration or the native runtime is unavailable.
     * @since 0.2.0
     */
    public function __invoke(ContainerInterface $container): NativeAdapter
    {
        $compatibility = $container->get(NativeCompatibility::class);
        if (!$compatibility instanceof NativeCompatibility) {
            throw new ExecutionRefused(RefusalCode::IncompatibleCapability);
        }
        return new NativeAdapter(NativeRuntime::create($compatibility), $compatibility);
    }
}
