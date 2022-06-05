<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;
use Klimick\Decode\Constraint as c;
use Klimick\Decode\Internal\Shape\ShapeDecoder;

/**
 * @mixin ProjectMetaMixin
 */
final class Project implements t\InferShape
{
    use t\ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return t\intersection(
            t\shape(
                id: t\int(),
                name: t\string()->constrained(c\maxLength(is: 10)),
            ),
            t\shape(description: t\string()->optional()),
        );
    }
}
