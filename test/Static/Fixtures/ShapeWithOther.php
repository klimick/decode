<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder as t;
use Klimick\Decode\Decoder\ShapeDecoder;

/**
 * @mixin ShapeWithOtherMetaMixin
 */
final class ShapeWithOther implements t\InferShape
{
    use t\ObjectInstance;

    public static function shape(): ShapeDecoder
    {
        return t\shape(
            project: Project::shape(),
            test: t\string(),
            userOrProject: UserOrProject::union(),
        );
    }
}
