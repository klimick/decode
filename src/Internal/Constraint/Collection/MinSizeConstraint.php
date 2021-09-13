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
final class MinSizeConstraint implements ConstraintInterface
{
    /**
     * @param positive-int $minSize
     */
    public function __construct(public int $minSize) { }

    public function name(): string
    {
        return 'MIN_SIZE';
    }

    public function payload(): array
    {
        return [
            'minSizeMustBe' => $this->minSize,
        ];
    }

    public function check(Context $context, mixed $value): Either
    {
        if (count($value) >= $this->minSize) {
            return valid();
        }

        return invalid($context, $this);
    }
}
