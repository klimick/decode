<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Fp\Functional\Either\Either;
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
     * @param T $value
     * @return Either<Invalid, Valid>
     */
    public function check(Context $context, mixed $value): Either;
}
