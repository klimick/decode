<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Numeric;

use Closure;
use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use function Klimick\Decode\Constraint\invalid;

/**
 * @implements ConstraintInterface<numeric>
 * @psalm-immutable
 */
final class ComparisonConstraint implements ConstraintInterface
{
    public const OP_LESS = 'LESS';
    public const OP_GREATER = 'GREATER';
    public const OP_LESS_OR_EQUAL = 'LESS_OR_EQUAL';
    public const OP_GREATER_OR_EQUAL = 'GREATER_OR_EQUAL';

    /**
     * @psalm-param ComparisonConstraint::OP_* $type
     * @param numeric $value
     */
    public function __construct(
        public string $type,
        public mixed $value,
    ) { }

    public function name(): string
    {
        return $this->type;
    }

    public function payload(): array
    {
        $prop = match ($this->type) {
            self::OP_LESS => 'mustBeLessThan',
            self::OP_GREATER => 'mustBeGreaterThan',
            self::OP_LESS_OR_EQUAL => 'mustBeLessOrEqualTo',
            self::OP_GREATER_OR_EQUAL => 'mustBeGreaterOrEqualTo',
        };

        return [$prop => $this->value];
    }

    public function check(Context $context, mixed $value): iterable
    {
        $op = self::getOp($this->type);

        if ($op($value, $this->value)) {
            return;
        }

        yield invalid($context, $this);
    }

    /**
     * @psalm-pure
     *
     * @psalm-param ComparisonConstraint::OP_* $type
     * @psalm-return pure-Closure(numeric, numeric): bool
     */
    private static function getOp(string $type): Closure
    {
        return match ($type) {
            self::OP_LESS => fn(mixed $a, mixed $b) => $a < $b,
            self::OP_GREATER => fn(mixed $a, mixed $b) => $a > $b,
            self::OP_LESS_OR_EQUAL => fn(mixed $a, mixed $b) => $a <= $b,
            self::OP_GREATER_OR_EQUAL => fn(mixed $a, mixed $b) => $a >= $b,
        };
    }
}
