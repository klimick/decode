<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Context;

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
    public function __construct(public mixed $item) {}

    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'IN_COLLECTION',
            payload: [
                'mustBePresent' => $this->item,
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        foreach ($value as $v) {
            if ($this->item === $v) {
                return;
            }
        }

        yield invalid($context);
    }
}
