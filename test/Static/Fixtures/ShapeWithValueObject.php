<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;
use Klimick\Decode\Decoder\ShapeDecoder;

/**
 * @mixin ShapeWithValueObjectMetaMixin
 */
final class ShapeWithValueObject implements t\InferShape
{
    use t\ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return t\shape(
            withSelf: t\mixed()->map(fn($value) => SomeValueObjectWithSelfKeyword::create($value)),
            withTypeHint: t\mixed()->map(fn($value) => SomeValueObjectWithTypeHint::create($value)),
        );
    }
}
