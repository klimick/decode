<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Boolean;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Constraint\Valid;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\valid;
use function Klimick\Decode\Constraint\invalids;

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
        $constraints = implode(', ', array_map(fn($c) => $c->name(), $this->constraints));

        return "ANY_OF({$constraints})";
    }

    public function payload(): array
    {
        return [];
    }

    public function check(Context $context, mixed $value): Either
    {
        $errors = [];

        foreach ($this->constraints as $constraint) {
            $result = $constraint
                ->check($context($constraint->name(), $value), $value)
                ->get();

            if ($result instanceof Valid) {
                return valid();
            }

            $errors = [...$errors, ...$result->errors];
        }

        return invalids($errors);
    }
}
