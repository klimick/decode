<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

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
    public function __construct(public int $maxLength) { }

    public function name(): string
    {
        return 'MAX_LENGTH';
    }

    public function payload(): array
    {
        return [
            'maxLengthMustBe' => $this->maxLength,
        ];
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (mb_strlen($value) <= $this->maxLength) {
            return;
        }

        yield invalid($context, $this);
    }
}
