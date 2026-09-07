<?php

declare(strict_types=1);

namespace Kumwe\Computation;

/**
 * Stable infrastructure refusals; business findings are successful result data.
 * @since 0.1.0
 */
enum RefusalCode: string
{
    /**
 * Stable wire token invalid_input.
 * @since 0.1.0
 */
    case InvalidInput = 'invalid_input';
    /**
 * Stable wire token unsupported_version.
 * @since 0.1.0
 */
    case UnsupportedVersion = 'unsupported_version';
    /**
 * Stable wire token incompatible_capability.
 * @since 0.1.0
 */
    case IncompatibleCapability = 'incompatible_capability';
    /**
 * Stable wire token incompatible_corpus.
 * @since 0.1.0
 */
    case IncompatibleCorpus = 'incompatible_corpus';
    /**
 * Stable wire token invalid_program.
 * @since 0.1.0
 */
    case InvalidProgram = 'invalid_program';
    /**
 * Stable wire token exhausted_limit.
 * @since 0.1.0
 */
    case ExhaustedLimit = 'exhausted_limit';
    /**
 * Stable wire token cancelled.
 * @since 0.1.0
 */
    case Cancelled = 'cancelled';
    /**
 * Stable wire token internal_failure.
 * @since 0.1.0
 */
    case InternalFailure = 'internal_failure';
}
