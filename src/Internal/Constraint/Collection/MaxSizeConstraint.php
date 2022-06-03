<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Collection;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;

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

    public function payload(): array
    {
        return [
            'maxSizeMustBe' => $this->maxSize,
        ];
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (count($value) <= $this->maxSize) {
            return;
        }

        yield invalid($context, $this);
    }
}
