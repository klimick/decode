<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;

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

    public function payload(): array
    {
        return ['mustBeEqualTo' => $this->equalTo];
    }

    public function check(Context $context, mixed $value): iterable
    {
        if ($value === $this->equalTo) {
            return;
        }

        yield invalid($context, $this);
    }
}
