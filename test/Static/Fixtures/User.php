<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder\Derive;
use Klimick\Decode\Decoder as t;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Psalm\Mixins\Klimick\Decode\Test\Static\Fixtures\UserProps;
use function Klimick\Decode\Constraint\maxLength;
use function Klimick\Decode\Constraint\minLength;

/**
 * @implements Derive\Props<User>
 * @mixin UserProps
 */
final class User implements Derive\Props
{
    use Derive\Decoder;

    public static function props(): ShapeDecoder
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
            minLength(is: 3),
            maxLength(is: 255),
        );
    }
}
