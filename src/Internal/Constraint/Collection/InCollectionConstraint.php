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

    public function check(Context $context, mixed $value): Either
    {
        return !in_array($this->item, $value, strict: true)
            ? invalid($context, $this, ['notInCollection' => $this->item])
            : valid();
    }
}
