<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

/**
 * @template T of numeric
 * @implements ConstraintInterface<T>
 * @psalm-immutable
 */
final class GtConstraint implements ConstraintInterface
{
    /**
     * @param T $value
     */
    public function __construct(public mixed $value) { }

    public function check(Context $context, mixed $value): Either
    {
        if ($value > $this->value) {
            return valid();
        }

        return invalid($context, 'NUMBER_EQ', ['expected' => $this->value]);
    }
}
