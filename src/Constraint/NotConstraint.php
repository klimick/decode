<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithNested;
use Klimick\Decode\Context;

/**
 * @template T
 * @implements ConstraintInterface<T>
 * @psalm-immutable
 */
final class NotConstraint implements ConstraintInterface
{
    /**
     * @param ConstraintInterface<T> $constraint
     */
    public function __construct(
        public ConstraintInterface $constraint,
    ) {}

    public function metadata(): ConstraintMetaWithNested
    {
        return ConstraintMetaWithNested::of(
            name: 'NOT',
            nested: $this->constraint->metadata(),
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        $hasErrors = false;

        foreach ($this->constraint->check($context($this->constraint, $value), $value) as $_) {
            $hasErrors = true;
            break;
        }

        if (!$hasErrors) {
            yield invalid($context($this->constraint, $value));
        }
    }
}
