<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures;

use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\RuntimeData;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

/**
 * @psalm-immutable
 */
final class Message extends RuntimeData
{
    protected static function properties(): AbstractDecoder
    {
        return shape(
            id: string(),
            senderId: string(),
            receiverId: string(),
        );
    }
}
