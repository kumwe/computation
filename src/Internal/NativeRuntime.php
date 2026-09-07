<?php

declare(strict_types=1);

namespace Kumwe\Computation\Internal;

use Kumwe\Computation\ExecutionRefused;
use Kumwe\Computation\NativeCompatibility;
use Kumwe\Computation\RefusalCode;
use Kumwe\Engine\Exception\BindingFailure;
use Kumwe\Engine\Runtime;
use ReflectionClass;

/**
 * Exact extension composition and bounded error translation; no semantic execution.
 * @internal
 * @since 0.2.0
 */
final class NativeRuntime
{
    /**
     * @param NativeCompatibility $compatibility Host-selected immutable tuple.
     * @return Runtime A fresh object owning request-local native handles.
     * @throws ExecutionRefused If the exact native implementation is unavailable.
     * @since 0.2.0
     */
    public static function create(NativeCompatibility $compatibility): Runtime
    {
        self::assertAvailable();
        $runtime = new Runtime();
        try {
            $compatibility->assertObserved($runtime->capabilities());
        } catch (BindingFailure $failure) {
            throw self::refusal($failure);
        }
        return $runtime;
    }

    /**
     * Check native ownership even when an adapter is directly constructed outside the provider.
     * @return void
     * @throws ExecutionRefused If a missing native class or userland shadow cannot prove extension ownership.
     * @since 0.2.0
     */
    public static function assertAvailable(): void
    {
        if (!extension_loaded('kumwe_engine') || !class_exists(Runtime::class, false)) {
            throw new ExecutionRefused(RefusalCode::IncompatibleCapability);
        }
        $class = new ReflectionClass(Runtime::class);
        if (!$class->isInternal() || $class->getExtensionName() !== 'kumwe_engine') {
            throw new ExecutionRefused(RefusalCode::IncompatibleCapability);
        }
    }

    /**
     * @param BindingFailure $failure Native status without retaining its message or previous exception.
     * @return ExecutionRefused Closed portable infrastructure category.
     * @since 0.2.0
     */
    public static function refusal(BindingFailure $failure): ExecutionRefused
    {
        return new ExecutionRefused(match ($failure->getCode()) {
            1 => RefusalCode::InvalidInput,
            2 => RefusalCode::UnsupportedVersion,
            3 => RefusalCode::IncompatibleCapability,
            4 => RefusalCode::IncompatibleCorpus,
            5 => RefusalCode::InvalidProgram,
            6 => RefusalCode::ExhaustedLimit,
            7 => RefusalCode::Cancelled,
            default => RefusalCode::InternalFailure,
        });
    }
}
