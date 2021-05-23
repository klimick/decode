<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Numeric;

use Klimick\Decode\Context;
use Klimick\Decode\Error\ConstraintError;
use Klimick\Decode\Internal\Constraint\ConstraintInterface;

/**
 * @template T of numeric
 * @implements ConstraintInterface<T>
 * @psalm-immutable
 */
final class EqConstraint implements ConstraintInterface
{
    /**
     * @param T $value
     */
    public function __construct(public mixed $value) { }

    public function createError(Context $context, mixed $value): ConstraintError
    {
        return new ConstraintError($context, 'NUMBER_EQ', ['expected' => $this->value]);
    }

    public function isValid(mixed $value): bool
    {
        return $this->value === $value;
    }
}
