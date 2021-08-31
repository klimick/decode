<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger\Owner;

use Klimick\Decode\Decoder\ProductType;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use function Klimick\Decode\Decoder\nonEmptyString;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\union;

/**
 * @psalm-immutable
 */
final class Customer extends ProductType
{
    protected static function definition(): ShapeDecoder
    {
        return shape(
            firstName: nonEmptyString(),
            secondName: nonEmptyString(),
            bio: union(null(), nonEmptyString()),
        );
    }
}
