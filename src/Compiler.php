<?php

declare(strict_types=1);

namespace Kumwe\Computation;

/**
 * Coarse compiler boundary; no production implementation is shipped in Phase 1A.
 * @since 0.1.0
 */
interface Compiler
{
    /**
 * Compile one complete program after ProgramEnvelope::assertPlan passes; refuse atomically.
 * @param ProgramEnvelope $program Opaque semantic source.
 * @param PlanIdentity $plan Exact source/profile/options/native tuple.
 * @param ExecutionLimits $limits Finite compile and output budgets.
 * @return CompiledProgram Opaque native-owned artifact matching the plan.
 * @throws ExecutionRefused On invalid input, incompatibility, cancellation or exhausted limits.
 * @since 0.1.0
 */
    public function compile(ProgramEnvelope $program, PlanIdentity $plan, ExecutionLimits $limits): CompiledProgram;
}
