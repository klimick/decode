<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\String;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;

/**
 * @implements ConstraintInterface<string>
 * @psalm-immutable
 */
final class EndsWithConstraint implements ConstraintInterface
{
    /**
     * @param non-empty-string $value
     */
    public function __construct(public string $value) { }

    public function name(): string
    {
        return 'ENDS_WITH';
    }

    public function payload(): array
    {
        return ['mustEndsWith' => $this->value];
    }

    public function check(Context $context, mixed $value): iterable
    {
        if (str_ends_with($value, $this->value)) {
            return;
        }

        yield invalid($context, $this);
    }
}
