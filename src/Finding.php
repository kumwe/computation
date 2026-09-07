<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Ordered machine finding; localization and business rule meanings remain downstream.
 * @since 0.1.0
 */
final readonly class Finding
{
    /**
     * @var array<string,string|int|bool|null> Detached sorted machine parameters.
     * @since 0.1.0
     */
    private array $parameters;

    /**
     * @param string $code Stable bounded finding token.
     * @param FindingSeverity $severity Severity independent of infrastructure status.
     * @param FindingPath $path Typed semantic value path.
     * @param SourceLocation $location Source program/rule position.
     * @param int $ordinal Stable declaration/execution order, not a lexical sort.
     * @param array<string,string|int|bool|null> $parameters Bounded machine data without objects.
     * @since 0.1.0
     */
    public function __construct(
        public string $code,
        public FindingSeverity $severity,
        public FindingPath $path,
        public SourceLocation $location,
        public int $ordinal,
        array $parameters = [],
    ) {
        Guard::token($code);
        Guard::integer($ordinal);
        Guard::require(count($parameters) <= 64);
        $copy = [];
        foreach ($parameters as $key => $value) {
            $name = Guard::token($key);
            Guard::require(preg_match('/^[0-9]+$/D', $name) !== 1);
            if (is_string($value)) {
                $value = Guard::text($value, 4096, true);
            } elseif (!is_int($value) && !is_bool($value) && $value !== null) {
                throw new ExecutionRefused(RefusalCode::InvalidInput);
            }
            $copy[$name] = $value;
        }
        ksort($copy, SORT_STRING);
        $this->parameters = $copy;
        Guard::require($this->parameterBytes() <= 16384, RefusalCode::ExhaustedLimit);
    }

    /**
     * @return array<string,string|int|bool|null> Detached parameters.
     * @since 0.1.0
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return int Parameter metadata bytes including keys, types and lengths.
     * @since 0.1.0
     */
    public function parameterBytes(): int
    {
        return strlen(Guard::encode($this->parameters));
    }

    /**
     * @return int All finding metadata bytes including path and parameters.
     * @since 0.1.0
     */
    public function byteSize(): int
    {
        return strlen(Guard::encode($this->toArray()));
    }

    /**
     * @param ExecutionLimits $limits Caller finding budgets.
     * @return void
     * @since 0.1.0
     */
    public function assertWithin(ExecutionLimits $limits): void
    {
        Guard::require(count($this->path->segments()) <= $limits->maxPathDepth
            && $this->parameterBytes() <= $limits->maxParameterBytes, RefusalCode::ExhaustedLimit);
    }

    /**
     * @return array<string,mixed> Exact ordered machine data.
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'code' => $this->code, 'severity' => $this->severity->value,
            'path' => $this->path->toArray(), 'location' => $this->location->toArray(),
            'ordinal' => $this->ordinal, 'parameters' => $this->parameters];
    }

    /**
     * @param array<string,mixed> $data Wire finding.
     * @return self Validated finding.
     * @since 0.1.0
     */
    public static function fromArray(array $data): self
    {
        Guard::shape($data, ['wire_version', 'code', 'severity', 'path', 'location', 'ordinal', 'parameters']);
        $severity = FindingSeverity::tryFrom(Guard::token($data['severity']));
        if ($severity === null) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        $parameters = $data['parameters'];
        if (!is_array($parameters)) {
            throw new ExecutionRefused(RefusalCode::InvalidInput);
        }
        Guard::require(count($parameters) <= 64);
        $checked = [];
        foreach ($parameters as $key => $value) {
            $key = Guard::token($key);
            if (!is_string($value) && !is_int($value) && !is_bool($value) && $value !== null) {
                throw new ExecutionRefused(RefusalCode::InvalidInput);
            }
            $checked[$key] = $value;
        }
        return new self(
            Guard::token($data['code']),
            $severity,
            FindingPath::fromArray(Guard::object($data['path'])),
            SourceLocation::fromArray(Guard::object($data['location'])),
            Guard::integer($data['ordinal']),
            $checked
        );
    }
}
