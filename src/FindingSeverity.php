<?php

declare(strict_types=1);

namespace Kumwe\Computation;

/**
 * Stable severity tokens do not turn a finding into infrastructure refusal.
 * @since 0.1.0
 */
enum FindingSeverity: string
{
    /**
 * Stable wire token info.
 * @since 0.1.0
 */
    case Info = 'info';
    /**
 * Stable wire token warning.
 * @since 0.1.0
 */
    case Warning = 'warning';
    /**
 * Stable wire token error.
 * @since 0.1.0
 */
    case Error = 'error';
}
