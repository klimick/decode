<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;
use Klimick\Decode\Decoder\ShapeDecoder;

/**
 * @mixin NewShapeByOtherMetaMixin
 */
final class NewShapeByOther implements t\InferShape
{
    use t\ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return Project::shape()->pick(['id', 'name']);
    }
}
