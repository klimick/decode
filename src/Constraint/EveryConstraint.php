<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithNested;
use Klimick\Decode\Context;

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
    public function __construct(public ConstraintInterface $constraint) {}

    public function metadata(): ConstraintMetaWithNested
    {
        return ConstraintMetaWithNested::of(
            name: 'EVERY',
            nested: $this->constraint->metadata(),
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        $hasErrors = false;

        foreach ($value as $k => $v) {
            foreach ($this->constraint->check($context($this->constraint, $v, $k), $v) as $_) {
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            yield invalid($context($this->constraint, $value));
        }
    }
}
