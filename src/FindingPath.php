<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Typed nested map keys and indexes; numeric string keys remain strings.
 * @since 0.1.0
 */
final readonly class FindingPath
{
    /**
     * @var list<string|int> Detached typed segments.
     * @since 0.1.0
     */
    private array $segments;

    /**
     * @param list<string|int> $segments Root is empty; indexes are nonnegative.
     * @since 0.1.0
     */
    public function __construct(array $segments)
    {
        Guard::require(array_is_list($segments) && count($segments) <= 64);
        $copy = [];
        foreach ($segments as $segment) {
            $copy[] = is_int($segment) ? Guard::integer($segment) : Guard::text($segment, 128, true);
        }
        $this->segments = $copy;
    }

    /**
     * @return list<string|int> Detached typed path.
     * @since 0.1.0
     */
    public function segments(): array
    {
        return $this->segments;
    }

    /**
     * @return int Encoded typed path metadata bytes.
     * @since 0.1.0
     */
    public function byteSize(): int
    {
        return strlen(Guard::encode($this->toArray()));
    }

    /**
     * @return array<string,mixed> Versioned path with native string/int distinctions.
     * @since 0.1.0
     */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'segments' => $this->segments];
    }

    /**
     * @param array<string,mixed> $data Serialized path.
     * @return self Validated path.
     * @since 0.1.0
     */
    public static function fromArray(array $data): self
    {
        Guard::shape($data, ['wire_version', 'segments']);
        $segments = [];
        foreach (Guard::items($data['segments'], 64) as $segment) {
            $segments[] = is_int($segment) ? Guard::integer($segment) : Guard::text($segment, 128, true);
        }
        return new self($segments);
    }
}
