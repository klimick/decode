<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\String;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use function Klimick\Decode\Constraint\valid;
use function Klimick\Decode\Constraint\invalid;

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

    public function check(Context $context, mixed $value): Either
    {
        if (mb_strlen($value) <= $this->maxLength) {
            return valid();
        }

        return invalid($context, $this, [
            'expected' => $this->maxLength,
            'actual' => mb_strlen($value),
        ]);
    }
}
