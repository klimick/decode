<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Boolean;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Constraint\Invalid;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\valid;
use function Klimick\Decode\Constraint\invalids;

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
        return 'ALL_OF';
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

            if ($result instanceof Invalid) {
                $errors = [...$errors, ...$result->errors];
            }
        }

        return !empty($errors) ? invalids($errors) : valid();
    }
}
