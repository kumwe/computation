<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;
use Kumwe\Computation\Internal\NativeRuntime;
use Kumwe\Engine\Exception\BindingFailure;
use Kumwe\Engine\Runtime;
use WeakMap;

/**
 * Coarse native compiler and executor; opaque payload bytes never become PHP semantic values.
 * @since 0.2.0
 */
final class NativeAdapter implements Compiler, Executor
{
    /**
     * @var WeakMap<CompiledProgram,string> Own-object native identifiers; no pointers or persisted bytecode.
     * @since 0.2.0
     */
    private WeakMap $plans;

    /**
     * @param Runtime $runtime Native object with bounded request-local plan ownership.
     * @param NativeCompatibility $compatibility Exact host-selected tuple.
     * @throws ExecutionRefused On an incompatible runtime.
     * @since 0.2.0
     */
    public function __construct(private Runtime $runtime, private readonly NativeCompatibility $compatibility)
    {
        NativeRuntime::assertAvailable();
        try {
            $compatibility->assertObserved($runtime->capabilities());
        } catch (BindingFailure $failure) {
            throw NativeRuntime::refusal($failure);
        }
        $this->plans = new WeakMap();
    }

    /**
     * @param ProgramEnvelope $program Complete opaque source bytes, parsed only inside Engine.
     * @param PlanIdentity $plan Exact source, host-generation, options and native tuple.
     * @param ExecutionLimits $limits Finite input and result budgets.
     * @return CompiledProgram Request-local native artifact; foreign or deserialized artifacts are refused.
     * @throws ExecutionRefused On invalid input, incompatible tuples or native refusal.
     * @since 0.2.0
     */
    public function compile(ProgramEnvelope $program, PlanIdentity $plan, ExecutionLimits $limits): CompiledProgram
    {
        $program->assertPlan($plan, $limits);
        Guard::require(
            $plan->capabilities->key() === $this->compatibility->capabilities->key(),
            RefusalCode::IncompatibleCapability,
        );
        try {
            $compiled = $this->runtime->compile([
                'wire_version' => 1,
                'profile' => $program->contract->profile,
                'corpus_digest' => $program->contract->corpusDigest,
                'program' => $program->bytes,
                'limits' => [
                    'max_input_bytes' => $limits->maxInputBytes,
                    'max_output_bytes' => $limits->maxOutputBytes,
                    'max_documents' => $limits->maxDocuments,
                    'max_findings' => $limits->maxFindings,
                    'max_instructions' => $limits->maxInstructions,
                    'max_milliseconds' => $limits->maxMilliseconds,
                ],
            ]);
        } catch (BindingFailure $failure) {
            throw NativeRuntime::refusal($failure);
        }
        $id = $compiled['plan_id'] ?? null;
        Guard::require(is_string($id) && preg_match('/^[0-9a-f]{32}$/D', $id) === 1, RefusalCode::InternalFailure);
        if (!is_string($id)) {
            throw new ExecutionRefused(RefusalCode::InternalFailure);
        }
        $result = new CompiledProgram($program, $plan, 'native-request-local-v1', $id, $limits);
        $this->plans[$result] = $id;
        return $result;
    }

    /**
     * Release an owned native plan when its operation or cache entry no longer needs execution.
     *
     * Long-lived consumers must release compiled plans to return bounded native capacity. Foreign,
     * copied and already released artifacts are refused; a native failure preserves PHP ownership.
     *
     * @param CompiledProgram $program Artifact returned by this same adapter instance.
     * @return void No execution or semantic transformation is performed.
     * @throws ExecutionRefused On a foreign/released artifact or native release refusal.
     * @since 0.3.0
     */
    public function release(CompiledProgram $program): void
    {
        $id = $this->plans[$program] ?? null;
        Guard::require($id !== null && $id === $program->bytes, RefusalCode::InvalidProgram);
        if (!is_string($id)) {
            throw new ExecutionRefused(RefusalCode::InvalidProgram);
        }
        try {
            $this->runtime->release($id);
        } catch (BindingFailure $failure) {
            throw NativeRuntime::refusal($failure);
        }
        unset($this->plans[$program]);
    }

    /**
     * @param CompiledProgram $program Artifact returned by this same adapter instance.
     * @param DocumentBatch $documents Complete ordered normalized opaque inputs.
     * @param ExecutionLimits $limits Finite batch budgets; checked again by Engine.
     * @return BatchResult Complete ordered native results and native-authored portable findings.
     * @throws ExecutionRefused On foreign artifacts, input mismatch, limits or native refusal.
     * @since 0.2.0
     */
    public function execute(CompiledProgram $program, DocumentBatch $documents, ExecutionLimits $limits): BatchResult
    {
        $program->assertCompatible($this->compatibility->capabilities, 'native-request-local-v1', $limits);
        $documents->assertForProgram($program, $limits);
        $id = $this->plans[$program] ?? null;
        Guard::require($id !== null && $id === $program->bytes, RefusalCode::InvalidProgram);
        $inputs = [];
        foreach ($documents->documents() as $document) {
            Guard::require(
                $document->contract->key() === $program->plan->contract->key(),
                RefusalCode::IncompatibleCorpus,
            );
            $inputs[] = ['correlation' => $document->correlation, 'input' => $document->bytes];
        }
        try {
            $output = $this->runtime->execute(['plan_id' => $id, 'batch' => [
                'wire_version' => 1,
                'documents' => $inputs,
                'limits' => [
                    'max_input_bytes' => $limits->maxInputBytes,
                    'max_output_bytes' => $limits->maxOutputBytes,
                    'max_documents' => $limits->maxDocuments,
                    'max_findings' => $limits->maxFindings,
                    'max_instructions' => $limits->maxInstructions,
                    'max_milliseconds' => $limits->maxMilliseconds,
                ],
            ]]);
        } catch (BindingFailure $failure) {
            throw NativeRuntime::refusal($failure);
        }
        Guard::require(($output['wire_version'] ?? null) === 1, RefusalCode::InternalFailure);
        $items = Guard::items($output['results'] ?? null, $limits->maxDocuments);
        Guard::require(count($items) === count($documents->documents()), RefusalCode::InternalFailure);
        $results = [];
        foreach ($items as $index => $item) {
            $item = Guard::object($item);
            $input = $documents->documents()[$index];
            Guard::require(($item['correlation'] ?? null) === $input->correlation, RefusalCode::InternalFailure);
            $bytes = $item['result_json'] ?? null;
            if (!is_string($bytes)) {
                throw new ExecutionRefused(RefusalCode::InternalFailure);
            }
            $findings = [];
            foreach (Guard::items($item['findings'] ?? null, $limits->maxFindings) as $finding) {
                $findings[] = Finding::fromArray(Guard::object($finding));
            }
            $results[] = new ExecutionResult($input->correlation, $input->contract, $bytes, $findings);
        }
        return new BatchResult($documents, $results, $limits);
    }

    /**
     * Prevent a second PHP object from inheriting another adapter's native artifact associations.
     * @return void
     * @throws ExecutionRefused Always; construct a fresh adapter through the explicit factory.
     * @since 0.2.0
     */
    public function __clone(): void
    {
        throw new ExecutionRefused(RefusalCode::InvalidProgram);
    }
}
