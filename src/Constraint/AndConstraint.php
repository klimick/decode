<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithNested;
use Klimick\Decode\Error\Context;
use function Fp\Collection\map;

/**
 * @template T
 * @implements ConstraintInterface<T>
 * @psalm-immutable
 */
final class AndConstraint implements ConstraintInterface
{
    /**
     * @param non-empty-list<ConstraintInterface<T>> $constraints
     */
    public function __construct(public array $constraints) { }

    public function metadata(): ConstraintMetaWithNested
    {
        return ConstraintMetaWithNested::of(
            name: 'AND',
            nested: map($this->constraints, fn(ConstraintInterface $c) => $c->metadata()),
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        foreach ($this->constraints as $constraint) {
            yield from $constraint->check($context($constraint, $value), $value);
        }
    }
}
