<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Context;

/**
 * @implements ConstraintInterface<array>
 * @psalm-immutable
 */
final class MinSizeConstraint implements ConstraintInterface
{
    /**
     * @param positive-int $minSize
     */
    public function __construct(public int $minSize) { }

    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'MIN_SIZE',
            payload: [
                'minSizeMustBe' => $this->minSize,
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (count($value) >= $this->minSize) {
            return;
        }

        yield invalid($context);
    }
}
