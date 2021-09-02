<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Semigroup\Semigroup;
use Klimick\Decode\Decoder\Valid;

/**
 * @extends Semigroup<Valid<array<string, mixed>>>
 * @psalm-immutable
 */
final class ShapePropertySemigroup extends Semigroup
{
    public function combine(mixed $lhs, mixed $rhs): Valid
    {
        return new Valid(array_merge($lhs->value, $rhs->value));
    }
}
