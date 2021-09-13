<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Collection;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

/**
 * @template T
 * @implements ConstraintInterface<array<array-key, T>>
 * @psalm-immutable
 */
final class InCollectionConstraint implements ConstraintInterface
{
    /**
     * @param T $item
     */
    public function __construct(public mixed $item) { }

    public function name(): string
    {
        return 'IN_COLLECTION';
    }

    public function payload(): array
    {
        return ['notInCollection' => $this->item];
    }

    public function check(Context $context, mixed $value): Either
    {
        foreach ($value as $v) {
            if ($this->item === $v) {
                return valid();
            }
        }

        return invalid($context, $this);
    }
}
