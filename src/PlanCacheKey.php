<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Complete deterministic plan metadata identity; owns no cache storage or invalidation.
 * @since 0.1.0
 */
final readonly class PlanCacheKey
{
    /**
 * @var string Lowercase SHA-256 cache identity.
 * @since 0.1.0
 */
    public string $value;

    /**
 * @param PlanIdentity $plan Complete plan identity.
 * @since 0.1.0
 */
    public function __construct(PlanIdentity $plan)
    {
        $this->value = hash('sha256', 'kumwe.computation.plan.v1' . Guard::encode($plan->toArray()));
    }
}
