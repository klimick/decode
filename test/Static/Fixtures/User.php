<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Constraint as c;
use Klimick\Decode\Decoder as t;
use Klimick\Decode\Decoder\ShapeDecoder;

/**
 * @mixin UserMetaMixin
 */
final class User implements t\InferShape
{
    use t\ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return t\shape(
            name: t\string()->constrained(
                c\minLength(is: 3),
                c\maxLength(is: 255),
            ),
            age: t\int(),
            projects: t\listOf(Project::type()),
        );
    }
}
