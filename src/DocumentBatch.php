<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Bounded ordered documents, detached from caller array references.
 * @since 0.1.0
 */
final readonly class DocumentBatch
{
    /**
     * @var list<DocumentInput> Exact immutable input order.
     * @since 0.1.0
     */
    private array $documents;

    /**
     * @param list<DocumentInput> $documents Complete input batch.
     * @param ExecutionLimits $limits Caller budgets.
     * @since 0.1.0
     */
    public function __construct(array $documents, ExecutionLimits $limits)
    {
        Guard::require(array_is_list($documents) && $documents !== [] && count($documents) <= 4096);
        $seen = [];
        $copy = [];
        foreach ($documents as $document) {
            if (!$document instanceof DocumentInput) {
                throw new ExecutionRefused(RefusalCode::InvalidInput);
            }
            Guard::require(!isset($seen[$document->correlation]));
            $seen[$document->correlation] = true;
            $copy[] = $document;
        }
        $this->documents = $copy;
        $this->assertWithin($limits);
    }

    /**
     * @return list<DocumentInput> Complete immutable ordered inputs.
     * @since 0.1.0
     */
    public function documents(): array
    {
        return $this->documents;
    }

    /**
     * @return int Opaque and transport metadata bytes counted before native input.
     * @since 0.1.0
     */
    public function byteSize(): int
    {
        $bytes = 0;
        foreach ($this->documents as $document) {
            $bytes += strlen($document->bytes) + strlen($document->correlation)
                + strlen(Guard::encode($document->contract->toArray()));
        }
        return $bytes;
    }

    /**
     * @param ExecutionLimits $limits Caller budgets.
     * @return void
     * @since 0.1.0
     */
    public function assertWithin(ExecutionLimits $limits): void
    {
        Guard::require(count($this->documents) <= $limits->maxDocuments
            && $this->byteSize() <= $limits->maxInputBytes, RefusalCode::ExhaustedLimit);
    }

    /**
     * Check all input profiles and combined artifact/document bytes before native execution.
     * @param CompiledProgram $program Exact compiled artifact.
     * @param ExecutionLimits $limits Caller budgets.
     * @return void
     * @since 0.1.0
     */
    public function assertForProgram(CompiledProgram $program, ExecutionLimits $limits): void
    {
        $this->assertWithin($limits);
        Guard::require(
            $this->byteSize() + strlen($program->bytes) <= $limits->maxInputBytes,
            RefusalCode::ExhaustedLimit
        );
        foreach ($this->documents as $document) {
            Guard::require(
                $program->plan->capabilities->supports($document->contract),
                RefusalCode::IncompatibleCorpus
            );
        }
    }

    /**
     * @return array<string,mixed> Versioned complete ordered batch.
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'documents' => array_map(
            static fn (DocumentInput $document): array => $document->toArray(),
            $this->documents
        )];
    }

    /**
     * @param array<string,mixed> $data Wire batch.
     * @param ExecutionLimits $limits Budgets.
     * @return self
     * @since 0.1.0
     */
    public static function fromArray(array $data, ExecutionLimits $limits): self
    {
        Guard::shape($data, ['wire_version', 'documents']);
        $inputs = Guard::items($data['documents'], min(4096, $limits->maxDocuments));
        $total = 0;
        foreach ($inputs as $input) {
            $input = Guard::object($input);
            Guard::shape($input, ['wire_version', 'correlation', 'contract', 'payload']);
            $contract = ContractIdentity::fromArray(Guard::object($input['contract']));
            $total += Guard::base64Size($input['payload'], min(16777216, $limits->maxInputBytes))
                + strlen(Guard::token($input['correlation'])) + strlen(Guard::encode($contract->toArray()));
            Guard::require($total <= $limits->maxInputBytes, RefusalCode::ExhaustedLimit);
        }
        $documents = [];
        foreach ($inputs as $document) {
            $documents[] = DocumentInput::fromArray(Guard::object($document), $limits);
        }
        return new self($documents, $limits);
    }
}
