<?php

declare(strict_types=1);

namespace Kumwe\Computation;

/**
 * Coarse whole-batch boundary; never calls PHP per field or expression.
 * @since 0.1.0
 */
interface Executor
{
    /**
     * Validate the exact artifact tuple and input/output budgets; execute the complete batch atomically.
     * @param CompiledProgram $program Opaque compiled artifact.
     * @param DocumentBatch $documents Prepared ordered documents.
     * @param ExecutionLimits $limits Finite execution and result budgets.
     * @return BatchResult One ordered matching result for every input.
     * @throws ExecutionRefused On invalid input, incompatibility, cancellation or exhausted limits.
     * @since 0.1.0
     */
    public function execute(CompiledProgram $program, DocumentBatch $documents, ExecutionLimits $limits): BatchResult;
}
