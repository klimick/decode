<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder\InferShape;
use Klimick\Decode\Decoder\ObjectInstance;
use Klimick\Decode\Decoder\ShapeDecoder;
use function Klimick\Decode\Decoder\listOf;
use function Klimick\Decode\Decoder\rec;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

/**
 * @mixin RecDepartmentMetaMixin
 */
final class RecBySelf implements InferShape
{
    use ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return shape(
            name: string(),
            subDepartments: listOf(rec(fn() => self::type())),
        );
    }
}
