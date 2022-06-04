<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder\Derive;
use Klimick\Decode\Decoder as t;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Psalm\Mixins\Klimick\Decode\Test\Static\Fixtures\PartialProjectProps;
use function Klimick\Decode\Constraint\maxLength;

/**
 * @implements Derive\Props<Project>
 * @mixin PartialProjectProps
 */
final class PartialProject implements Derive\Props
{
    use Derive\Decoder;

    public static function props(): ShapeDecoder
    {
        return t\partialShape(
            id: t\int(),
            name: t\string()->constrained(maxLength(is: 10)),
        );
    }
}
