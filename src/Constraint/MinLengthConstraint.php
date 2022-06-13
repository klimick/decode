<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaWithPayload;
use Klimick\Decode\Error\Context;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class MinLengthConstraint implements ConstraintInterface
{
    /**
     * @param positive-int $minLength
     */
    public function __construct(public int $minLength) { }

    public function metadata(): ConstraintMetaWithPayload
    {
        return ConstraintMetaWithPayload::of(
            name: 'MIN_LENGTH',
            payload: [
                'minLengthMustBe' => $this->minLength,
            ],
        );
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (mb_strlen($value) >= $this->minLength) {
            return;
        }

        yield invalid($context);
    }
}
