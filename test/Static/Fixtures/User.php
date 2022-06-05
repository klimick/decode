<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;
use Klimick\Decode\Constraint as c;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Psalm\Mixins\Klimick\Decode\Test\Static\Fixtures\UserProps;

/**
 * @implements t\InferShape<User>
 * @mixin UserProps
 */
final class User implements t\InferShape
{
    use t\ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return t\shape(
            name: self::complexType(),
            age: t\int(),
            projects: t\listOf(Project::type()),
        );
    }

    /**
     * @return t\DecoderInterface<string>
     * @psalm-pure
     */
    public static function complexType(): t\DecoderInterface
    {
        return t\string()->constrained(
            c\minLength(is: 3),
            c\maxLength(is: 255),
        );
    }
}
