<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Semigroup\Semigroup;
use Klimick\Decode\Decoder\Invalid;

/**
 * @extends Semigroup<Invalid>
 * @psalm-immutable
 */
final class ShapeErrorSemigroup extends Semigroup
{
    public function combine(mixed $lhs, mixed $rhs): Invalid
    {
        return new Invalid([...$lhs->errors, ...$rhs->errors]);
    }
}
