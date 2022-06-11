<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;

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
        return "EXISTS.{$this->constraint->name()}";
    }

    public function payload(): array
    {
        return $this->constraint->payload();
    }

    public function check(Context $context, mixed $value): iterable
    {
        foreach ($value as $k => $v) {
            $hasErrors = false;

            foreach ($this->constraint->check($context($this->constraint, $v, $k), $v) as $_) {
                $hasErrors = true;
            }

            if (!$hasErrors) {
                return;
            }
        }

        yield invalid(
            context: $context($this->constraint, $value),
            constraint: $this->constraint,
        );
    }
}
