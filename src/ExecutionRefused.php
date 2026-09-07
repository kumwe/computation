<?php

declare(strict_types=1);

namespace Kumwe\Computation;

/**
 * Safe stable failure without a user-supplied message or previous payload exception.
 * @since 0.1.0
 */
final class ExecutionRefused extends \RuntimeException
{
    /**
 * Construct a bounded diagnostic from a closed refusal code only.
 * @param RefusalCode $reason Infrastructure failure category.
 * @since 0.1.0
 */
    public function __construct(public readonly RefusalCode $reason)
    {
        parent::__construct('Computation request refused: ' . $reason->value . '.');
    }
}
