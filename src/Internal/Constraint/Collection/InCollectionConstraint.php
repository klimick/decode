<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint\Collection;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use function Klimick\Decode\Constraint\invalid;

/**
 * @template T
 * @implements ConstraintInterface<array<array-key, T>>
 * @psalm-immutable
 */
final class InCollectionConstraint implements ConstraintInterface
{
    /**
     * @param T $item
     */
    public function __construct(public mixed $item) { }

    public function name(): string
    {
        return 'IN_COLLECTION';
    }

    public function payload(): array
    {
        return ['mustBePresent' => $this->item];
    }

    public function check(Context $context, mixed $value): iterable
    {
        foreach ($value as $v) {
            if ($this->item === $v) {
                return;
            }
        }

        yield invalid($context, $this);
    }
}
