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
     * @param ConstraintInterface<TVal> $constraint
     */
    public function __construct(public ConstraintInterface $constraint) { }

    public function name(): string
    {
        return 'EXISTS';
    }

    public function check(Context $context, mixed $value): Either
    {
        $errors = [];

        foreach ($value as $k => $v) {
            $result = $this->constraint
                ->check($context($this->constraint->name(), $v, (string) $k), $v)
                ->get();

            if ($result instanceof Valid) {
                return valid();
            }

            $errors = [...$errors, ...$result->errors];
        }

        return !empty($errors) ? invalids($errors) : valid();
    }
}
