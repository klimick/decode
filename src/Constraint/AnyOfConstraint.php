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
final class AnyOfConstraint implements ConstraintInterface
{
    /**
     * @param non-empty-list<ConstraintInterface<T>> $constraints
     */
    public function __construct(public array $constraints) { }

    public function name(): string
    {
        $constraints = implode(', ', map($this->constraints, fn($c) => $c->name()));

        return "ANY_OF({$constraints})";
    }

    public function payload(): array
    {
        return map($this->constraints, fn(ConstraintInterface $c) => $c->payload());
    }

    public function check(Context $context, mixed $value): iterable
    {
        $hasErrors = false;

        foreach ($this->constraints as $constraint) {
            foreach ($constraint->check($context($constraint, $value), $value) as $error) {
                yield $error;
                $hasErrors = true;
            }

            if (!$hasErrors) {
                return;
            }
        }
    }
}
