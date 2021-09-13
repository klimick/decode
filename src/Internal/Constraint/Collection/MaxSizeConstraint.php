<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Collection;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

/**
 * @implements ConstraintInterface<array>
 * @psalm-immutable
 */
final class MaxSizeConstraint implements ConstraintInterface
{
    /**
     * @param positive-int $maxSize
     */
    public function __construct(public int $maxSize) { }

    public function name(): string
    {
        return 'MAX_SIZE';
    }

    public function check(Context $context, mixed $value): Either
    {
        $actualSize = count($value);

        if ($actualSize <= $this->maxSize) {
            return valid();
        }

        return invalid($context, $this, [
            'expected' => $this->maxSize,
            'actual' => $actualSize,
        ]);
    }
}
