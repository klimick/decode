<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Collection;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Constraint\Valid;
use function Klimick\Decode\Constraint\invalids;
use function Klimick\Decode\Constraint\valid;

/**
 * @template TVal
 * @implements ConstraintInterface<array<array-key, TVal>>
 * @psalm-immutable
 */
final class ExistsConstraint implements ConstraintInterface
{
    /**
     * @param non-empty-list<ConstraintInterface<TVal>> $constraints
     */
    public function __construct(public array $constraints) { }

    public function name(): string
    {
        return 'EXISTS';
    }

    public function check(Context $context, mixed $value): Either
    {
        $errors = [];

        foreach ($value as $k => $v) {
            foreach ($this->constraints as $constraint) {
                $result = $constraint
                    ->check($context->append($constraint->name(), $v, (string) $k), $v)
                    ->get();

                if ($result instanceof Valid) {
                    return valid();
                }

                $errors = [...$errors, ...$result->errors];
            }
        }

        return !empty($errors) ? invalids($errors) : valid();
    }
}
