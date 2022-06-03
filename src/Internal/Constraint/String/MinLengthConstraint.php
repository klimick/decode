<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\String;

use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use function Klimick\Decode\Constraint\invalid;

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

    public function name(): string
    {
        return 'MIN_LENGTH';
    }

    public function payload(): array
    {
        return [
            'minLengthMustBe' => $this->minLength,
        ];
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (mb_strlen($value) >= $this->minLength) {
            return;
        }

        yield invalid($context, $this);
    }
}
