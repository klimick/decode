<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger\Owner;

use Klimick\Decode\Decoder\RuntimeData;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use function Klimick\Decode\Decoder\nonEmptyString;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\union;

/**
 * @psalm-immutable
 */
final class Customer extends RuntimeData
{
    protected static function properties(): ShapeDecoder
    {
        return shape(
            firstName: nonEmptyString(),
            secondName: nonEmptyString(),
            bio: union(null(), nonEmptyString()),
        );
    }
}
