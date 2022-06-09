<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;
use Klimick\Decode\Decoder\ShapeDecoder;

/**
 * @mixin IntersectionWithOtherPickMetaMixin
 */
final class IntersectionWithOtherPick implements t\InferShape
{
    use t\ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return t\intersection(
            Project::shape()->pick(['id', 'name']),
            t\shape(
                test: t\string(),
            ),
        );
    }
}
