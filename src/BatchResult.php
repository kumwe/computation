<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Atomic ordered batch correspondence and caller output budgets.
 * @since 0.1.0
 */
final readonly class BatchResult
{
    /**
     * @var list<ExecutionResult> Complete ordered results, never partial output.
     * @since 0.1.0
     */
    private array $results;

    /**
     * @param DocumentBatch $expected Exact original batch, required for correlation validation.
     * @param list<ExecutionResult> $results One result per document in identical order.
     * @param ExecutionLimits $limits Caller input/output and finding budgets.
     * @since 0.1.0
     */
    public function __construct(DocumentBatch $expected, array $results, ExecutionLimits $limits)
    {
        $expected->assertWithin($limits);
        Guard::require(array_is_list($results) && count($results) === count($expected->documents()));
        $copy = [];
        $bytes = 0;
        $findings = 0;
        foreach ($results as $index => $result) {
            if (!$result instanceof ExecutionResult) {
                throw new ExecutionRefused(RefusalCode::InvalidInput);
            }
            $document = $expected->documents()[$index];
            Guard::require($result->correlation === $document->correlation
                && $result->contract->key() === $document->contract->key(), RefusalCode::InvalidInput);
            $bytes += $result->byteSize();
            $findings += count($result->findings());
            Guard::require(
                $bytes <= $limits->maxOutputBytes && $findings <= $limits->maxFindings,
                RefusalCode::ExhaustedLimit
            );
            foreach ($result->findings() as $finding) {
                $finding->assertWithin($limits);
            }
            $copy[] = $result;
        }
        $this->results = $copy;
    }

    /**
     * @return list<ExecutionResult> Complete detached immutable results.
     * @since 0.1.0
     */
    public function results(): array
    {
        return $this->results;
    }

    /**
     * @return array<string,mixed> Complete versioned batch result.
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'results' => array_map(
            static fn (ExecutionResult $result): array => $result->toArray(),
            $this->results
        )];
    }

    /**
     * @param array<string,mixed> $data Wire batch result.
     * @param DocumentBatch $expected Original request, not data supplied by an untrusted result.
     * @param ExecutionLimits $limits Caller budgets.
     * @return self Validated complete result.
     * @since 0.1.0
     */
    public static function fromArray(array $data, DocumentBatch $expected, ExecutionLimits $limits): self
    {
        Guard::shape($data, ['wire_version', 'results']);
        $wireResults = Guard::items($data['results'], min(4096, $limits->maxDocuments));
        Guard::require(count($wireResults) === count($expected->documents()));
        $bytes = 0;
        $count = 0;
        foreach ($wireResults as $index => $record) {
            $record = Guard::object($record);
            Guard::shape($record, ['wire_version', 'correlation', 'contract', 'payload', 'findings']);
            $correlation = Guard::token($record['correlation']);
            $contract = ContractIdentity::fromArray(Guard::object($record['contract']));
            $input = $expected->documents()[$index];
            Guard::require($correlation === $input->correlation && $contract->key() === $input->contract->key());
            $bytes += Guard::base64Size($record['payload'], min(16777216, $limits->maxOutputBytes))
                + strlen($correlation) + strlen(Guard::encode($contract->toArray()));
            foreach (Guard::items($record['findings'], min(65536, $limits->maxFindings)) as $finding) {
                $count++;
                Guard::require($count <= $limits->maxFindings, RefusalCode::ExhaustedLimit);
                $finding = Finding::fromArray(Guard::object($finding));
                $finding->assertWithin($limits);
                $bytes += $finding->byteSize();
                Guard::require($bytes <= $limits->maxOutputBytes, RefusalCode::ExhaustedLimit);
            }
            Guard::require($bytes <= $limits->maxOutputBytes, RefusalCode::ExhaustedLimit);
        }
        $results = [];
        foreach ($wireResults as $result) {
            $results[] = ExecutionResult::fromArray(Guard::object($result), $limits);
        }
        return new self($expected, $results, $limits);
    }
}
