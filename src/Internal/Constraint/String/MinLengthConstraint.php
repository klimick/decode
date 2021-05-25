<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\String;

use Klimick\Decode\Constraint\ConstraintError;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class MinLengthConstraint implements ConstraintInterface
{
    /**
     * @param positive-int $minLength
     */
    public function __construct(public int $minLength) { }

    public function createError(Context $context, mixed $value): ConstraintError
    {
        return new ConstraintError($context, 'MIN_LENGTH', [
            'expected' => $this->minLength,
            'actual' => mb_strlen($value),
        ]);
    }

    public function isValid(mixed $value): bool
    {
        return mb_strlen($value) >= $this->minLength;
    }
}
