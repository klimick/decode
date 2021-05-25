<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;

/**
 * @template T
 * @psalm-immutable
 */
interface ConstraintInterface
{
    /**
     * @param T $value
     */
    public function createError(Context $context, mixed $value): ConstraintError;

    /**
     * @param T $value
     */
    public function isValid(mixed $value): bool;
}
