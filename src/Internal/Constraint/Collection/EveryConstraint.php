<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Collection;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;

/**
 * @template TVal
 * @implements ConstraintInterface<array<array-key, TVal>>
 * @psalm-immutable
 */
final class EveryConstraint implements ConstraintInterface
{
    /**
     * @param ConstraintInterface<TVal> $constraint
     */
    public function __construct(public ConstraintInterface $constraint) { }

    public function name(): string
    {
        return "FORALL.{$this->constraint->name()}";
    }

    public function payload(): array
    {
        return $this->constraint->payload();
    }

    public function check(Context $context, mixed $value): iterable
    {
        $hasErrors = false;

        foreach ($value as $k => $v) {
            foreach ($this->constraint->check($context($this->constraint->name(), $v, (string) $k), $v) as $_) {
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            yield invalid(
                context: $context($this->constraint->name(), $value),
                constraint: $this->constraint,
            );
        }
    }
}
