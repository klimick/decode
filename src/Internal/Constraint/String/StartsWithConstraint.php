<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\String;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use function Klimick\Decode\Constraint\invalid;
use function Klimick\Decode\Constraint\valid;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class StartsWithConstraint implements ConstraintInterface
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(public string $value) { }

    public function name(): string
    {
        return 'STARTS_WITH';
    }

    public function payload(): array
    {
        return ['expected' => $this->value];
    }

    public function check(Context $context, mixed $value): Either
    {
        return !str_starts_with($value, $this->value)
            ? invalid($context, $this, $this->payload())
            : valid();
    }
}
