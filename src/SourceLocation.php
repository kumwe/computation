<?php

declare(strict_types=1);

namespace Kumwe\Computation;

use Kumwe\Computation\Internal\Guard;

/**
 * Portable source location without ownership of an AST.
 * @since 0.1.0
 */
final readonly class SourceLocation
{
    /**
 * Construct a complete validated value; does not verify an upstream release.
 * @param string $unit unit.
 * @param string $rule rule.
 * @param int $ordinal ordinal.
 * @throws ExecutionRefused If a value violates this boundary.
 * @since 0.1.0
 */
    public function __construct(
        public string $unit,
        public string $rule,
        public int $ordinal,
    ) {
        Guard::token($unit);
        Guard::token($rule);
        Guard::integer($ordinal);
    }

    /**
 * @return array<string,mixed> Versioned portable record in prescribed field order.
 * @since 0.1.0
 */
    public function toArray(): array
    {
        return ['wire_version' => 1, 'unit' => $this->unit, 'rule' => $this->rule, 'ordinal' => $this->ordinal];
    }

    /**
 * @param array<string,mixed> $data Exact wire record.
 * @return self Validated value.
 * @since 0.1.0
 */
    public static function fromArray(array $data): self
    {
        Guard::shape($data, ['wire_version', 'unit', 'rule', 'ordinal']);
        return new self(Guard::token($data['unit']), Guard::token($data['rule']), Guard::integer($data['ordinal']));
    }
}
