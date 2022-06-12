<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Constraint\Metadata\ConstraintMetaInterface;
use Klimick\Decode\Context;

/**
 * @template-covariant T
 * @psalm-immutable
 */
interface ConstraintInterface
{
    public function metadata(): ConstraintMetaInterface;

    /**
     * @param T $value
     * @return iterable<array-key, ConstraintError>
     */
    public function check(Context $context, mixed $value): iterable;
}
