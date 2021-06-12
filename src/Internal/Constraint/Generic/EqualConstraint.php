<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Generic;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

/**
 * @template T
 * @implements ConstraintInterface<T>
 * @psalm-immutable
 */
final class EqualConstraint implements ConstraintInterface
{
    /**
     * @param T $equalTo
     */
    public function __construct(public mixed $equalTo) { }

    public function name(): string
    {
        return 'EQUAL';
    }

    public function check(Context $context, mixed $value): Either
    {
        return $value !== $this->equalTo
            ? invalid($context, $this, ['expected' => $this->equalTo])
            : valid();
    }
}
