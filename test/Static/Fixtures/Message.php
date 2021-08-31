<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder\ProductType;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

/**
 * @psalm-immutable
 */
final class Message extends ProductType
{
    protected static function properties(): ShapeDecoder
    {
        return shape(
            id: string(),
            senderId: string(),
            receiverId: string(),
        );
    }
}
