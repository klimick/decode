<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Error\Context;

/**
 * @implements ConstraintInterface<array>
 * @psalm-immutable
 */
final class MaxSizeConstraint implements ConstraintInterface
{
    /**
     * @param positive-int $maxSize
     */
    public function __construct(
        public int $maxSize,
    ) {}

    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'MAX_SIZE',
            payload: [
                'maxSizeMustBe' => $this->maxSize,
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (count($value) <= $this->maxSize) {
            return;
        }

        yield invalid($context);
    }
}
