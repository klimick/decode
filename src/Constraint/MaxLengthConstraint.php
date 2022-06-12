<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Context;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class MaxLengthConstraint implements ConstraintInterface
{
    /**
     * @param positive-int $maxLength
     */
    public function __construct(
        public int $maxLength,
    ) {}

    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'MAX_LENGTH',
            payload: [
                'maxLengthMustBe' => $this->maxLength,
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (mb_strlen($value) <= $this->maxLength) {
            return;
        }

        yield invalid($context);
    }
}
