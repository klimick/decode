<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger;

use Klimick\Decode\Decoder\RuntimeData;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Owner;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

/**
 * @psalm-immutable
 */
final class Whatsapp extends RuntimeData
{
    protected static function properties(): ShapeDecoder
    {
        return shape(
            phone: string(),
            owner: Owner::type(),
        );
    }
}