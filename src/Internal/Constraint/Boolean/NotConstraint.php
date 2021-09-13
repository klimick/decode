<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Boolean;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Constraint\Valid;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\valid;
use function Klimick\Decode\Constraint\invalid;

/**
 * @template T
 * @implements ConstraintInterface<T>
 * @psalm-immutable
 */
final class NotConstraint implements ConstraintInterface
{
    /**
     * @param ConstraintInterface<T> $constraint
     */
    public function __construct(public ConstraintInterface $constraint) { }

    public function name(): string
    {
        return 'NOT';
    }

    public function payload(): array
    {
        return $this->constraint->payload();
    }

    public function check(Context $context, mixed $value): Either
    {
        $result = $this->constraint
            ->check($context, $value)
            ->get();

        return $result instanceof Valid
            ? invalid(
                context: $context($this->constraint->name(), $value),
                constraint: $this->constraint,
                payload: $this->constraint->payload(),
            )
            : valid();
    }
}
