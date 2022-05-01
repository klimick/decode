<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder\Derive;
use Klimick\Decode\Decoder as t;
use Psalm\Mixins\Klimick\Decode\Test\Static\Fixtures\UserProps;
use function Klimick\Decode\Constraint\maxLength;

/**
 * @implements Derive\Props<User>
 * @mixin UserProps
 */
final class User implements Derive\Props
{
    use Derive\Create;

    public static function props(): t\DecoderInterface
    {
        return t\shape(
            name: t\string()->constrained(maxLength(is: 10)),
            age: t\int(),
            projects: t\listOf(Project::type()),
        );
    }
}
