<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;

/**
 * @template-covariant T
 * @psalm-immutable
 */
interface ConstraintInterface
{
    /**
     * @return non-empty-string
     */
    public function name(): string;

    /**
     * @return array<array-key, mixed>
     */
    public function payload(): array;

    /**
     * @param T $value
     * @return iterable<array-key, ConstraintError>
     */
    public function check(Context $context, mixed $value): iterable;
}
