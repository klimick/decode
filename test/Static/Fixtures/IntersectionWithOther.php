<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;
use Klimick\Decode\Decoder\ShapeDecoder;

/**
 * @mixin IntersectionWithOtherMetaMixin
 */
final class IntersectionWithOther implements t\InferShape
{
    use t\ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return t\intersection(
            Project::shape(),
            t\shape(
                test: t\string(),
            ),
        );
    }
}
