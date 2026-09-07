<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Finite caller budgets; validates requests without running clocks, cancellation or algorithms.
 * @since 0.1.0
 */
final readonly class ExecutionLimits
{
    /**
     * Construct a complete validated value; does not verify an upstream release.
     * @param int $maxInputBytes maxInputBytes.
     * @param int $maxOutputBytes maxOutputBytes.
     * @param int $maxDocuments maxDocuments.
     * @param int $maxFindings maxFindings.
     * @param int $maxPathDepth maxPathDepth.
     * @param int $maxParameterBytes maxParameterBytes.
     * @param int $maxInstructions maxInstructions.
     * @param int $maxMilliseconds maxMilliseconds.
     * @throws ExecutionRefused If a value violates this boundary.
     * @since 0.1.0
     */
    public function __construct(
        public int $maxInputBytes = 16777216,
        public int $maxOutputBytes = 16777216,
        public int $maxDocuments = 1024,
        public int $maxFindings = 4096,
        public int $maxPathDepth = 32,
        public int $maxParameterBytes = 4096,
        public int $maxInstructions = 100000000,
        public int $maxMilliseconds = 30000,
    ) {
        Guard::integer($maxInputBytes, 1, 67108864);
        Guard::integer($maxOutputBytes, 1, 67108864);
        Guard::integer($maxDocuments, 1, 4096);
        Guard::integer($maxFindings, 1, 65536);
        Guard::integer($maxPathDepth, 1, 64);
        Guard::integer($maxParameterBytes, 1, 16384);
        Guard::integer($maxInstructions, 1, 1000000000);
        Guard::integer($maxMilliseconds, 1, 600000);
    }

    /**
     * @return array<string,mixed> Versioned portable record in prescribed field order.
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return ['wire_version' => 1,
             'max_input_bytes' => $this->maxInputBytes,
             'max_output_bytes' => $this->maxOutputBytes,
             'max_documents' => $this->maxDocuments,
             'max_findings' => $this->maxFindings,
             'max_path_depth' => $this->maxPathDepth,
             'max_parameter_bytes' => $this->maxParameterBytes,
             'max_instructions' => $this->maxInstructions,
             'max_milliseconds' => $this->maxMilliseconds];
    }

    /**
     * @param array<string,mixed> $data Exact wire record.
     * @return self Validated value.
     * @since 0.1.0
     */
    public static function fromArray(array $data): self
    {
        Guard::shape(
            $data,
            ['wire_version',
             'max_input_bytes',
             'max_output_bytes',
             'max_documents',
             'max_findings',
             'max_path_depth',
             'max_parameter_bytes',
             'max_instructions',
            'max_milliseconds']
        );
        return new self(
            Guard::integer(
                $data['max_input_bytes'],
                1
            ),
            Guard::integer(
                $data['max_output_bytes'],
                1
            ),
            Guard::integer(
                $data['max_documents'],
                1
            ),
            Guard::integer(
                $data['max_findings'],
                1
            ),
            Guard::integer(
                $data['max_path_depth'],
                1
            ),
            Guard::integer(
                $data['max_parameter_bytes'],
                1
            ),
            Guard::integer(
                $data['max_instructions'],
                1
            ),
            Guard::integer(
                $data['max_milliseconds'],
                1
            )
        );
    }
}
