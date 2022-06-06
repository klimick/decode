<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;
use function Fp\Collection\map;

/**
 * @template T
 * @implements ConstraintInterface<T>
 * @psalm-immutable
 */
final class AllOfConstraint implements ConstraintInterface
{
    /**
     * @param non-empty-list<ConstraintInterface<T>> $constraints
     */
    public function __construct(public array $constraints) { }

    public function name(): string
    {
        $constraints = implode(', ', map($this->constraints, fn($c) => $c->name()));

        return "ALL_OF({$constraints})";
    }

    public function payload(): array
    {
        return map($this->constraints, fn(ConstraintInterface $c) => $c->payload());
    }

    public function check(Context $context, mixed $value): iterable
    {
        foreach ($this->constraints as $constraint) {
            yield from $constraint->check($context($constraint->name(), $value), $value);
        }
    }
}
