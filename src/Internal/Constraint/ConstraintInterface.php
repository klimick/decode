<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Constraint;

use Klimick\Decode\Context;
use Klimick\Decode\Error\ConstraintError;

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
