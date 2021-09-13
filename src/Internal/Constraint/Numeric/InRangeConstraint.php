<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Numeric;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

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
            'from' => $this->from,
            'to' => $this->to,
        ];
    }

    public function check(Context $context, mixed $value): Either
    {
        if ($this->from <= $value && $value <= $this->to) {
            return valid();
        }

        return invalid($context, $this, $this->payload());
    }
}
