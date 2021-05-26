<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint;

use Closure;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

/**
 * @implements ConstraintInterface<numeric>
 * @psalm-immutable
 */
final class ComparisonConstraint implements ConstraintInterface
{
    public const OP_EQUAL = 'EQUAL';
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

    public function check(Context $context, mixed $value): Either
    {
        return !(self::getOp($this->type))($value, $this->value)
            ? invalid($context, $this, ['expected' => $this->value])
            : valid();
    }

    /**
     * @psalm-pure
     *
     * @psalm-param ComparisonConstraint::OP_* $type
     * @return Closure(numeric, numeric): bool
     */
    private static function getOp(string $type): Closure
    {
        return match ($type) {
            self::OP_LESS => fn(mixed $a, mixed $b) => $a < $b,
            self::OP_GREATER => fn(mixed $a, mixed $b) => $a > $b,
            self::OP_EQUAL => fn(mixed $a, mixed $b) => $a === $b,
            self::OP_LESS_OR_EQUAL => fn(mixed $a, mixed $b) => $a <= $b,
            self::OP_GREATER_OR_EQUAL => fn(mixed $a, mixed $b) => $a >= $b,
        };
    }
}
