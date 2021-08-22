<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger\Owner;

use Klimick\Decode\Decoder\RuntimeData;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use function Klimick\Decode\Decoder\literal;
use function Klimick\Decode\Decoder\nonEmptyString;
use function Klimick\Decode\Decoder\shape;

/**
 * @psalm-immutable
 */
final class Bot extends RuntimeData
{
    protected static function properties(): ShapeDecoder
    {
        return shape(
            token: nonEmptyString(),
            apiVersion: literal('v1', 'v2', 'v3'),
        );
    }
}
