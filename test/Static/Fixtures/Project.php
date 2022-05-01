<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\Derive;
use Klimick\Decode\Decoder as t;
use Psalm\Mixins\Klimick\Decode\Test\Static\Fixtures\ProjectProps;
use function Klimick\Decode\Constraint\maxLength;

/**
 * @implements Derive\Props<Project>
 * @mixin ProjectProps
 */
final class Project implements Derive\Props
{
    use Derive\Create;

    public static function props(): DecoderInterface
    {
        return t\shape(
            id: t\int(),
            name: t\string()->constrained(maxLength(is: 10)),
        );
    }
}
