<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Numeric;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;

/**
 * @implements ConstraintInterface<numeric>
 * @psalm-immutable
 */
final class InRangeConstraint implements ConstraintInterface
{
    /**
     * @param numeric $from
     * @param numeric $to
     */
    public function __construct(
        public mixed $from,
        public mixed $to,
    ) { }

    public function name(): string
    {
        return 'IN_RANGE';
    }

    public function payload(): array
    {
        return [
            'mustBeGreaterOrEqualTo' => $this->from,
            'mustBeLessOrEqualTo' => $this->to,
        ];
    }

    public function check(Context $context, mixed $value): iterable
    {
        if ($this->from <= $value && $value <= $this->to) {
            return;
        }

        yield invalid($context, $this);
    }
}
