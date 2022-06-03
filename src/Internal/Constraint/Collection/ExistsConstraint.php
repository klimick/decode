<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Collection;

use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use function Klimick\Decode\Constraint\invalid;

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

            foreach ($this->constraint->check($context($this->constraint->name(), $v, (string) $k), $v) as $_) {
                $hasErrors = true;
            }

            if (!$hasErrors) {
                return;
            }
        }

        yield invalid(
            context: $context($this->constraint->name(), $value),
            constraint: $this->constraint,
        );
    }
}
