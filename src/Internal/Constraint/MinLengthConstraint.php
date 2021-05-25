<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use function Klimick\Decode\Constraint\valid;
use function Klimick\Decode\Constraint\invalid;

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

    public function check(Context $context, mixed $value): Either
    {
        if (mb_strlen($value) >= $this->minLength) {
            return valid();
        }

        return invalid($context, 'MIN_LENGTH', [
            'expected' => $this->minLength,
            'actual' => mb_strlen($value),
        ]);
    }
}
