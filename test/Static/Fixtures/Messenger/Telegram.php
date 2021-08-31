<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Fixtures\Messenger;

use Klimick\Decode\Decoder\ProductType;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Owner;
use function Klimick\Decode\Constraint\maxLength;
use function Klimick\Decode\Constraint\minLength;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

/**
 * @psalm-immutable
 */
final class Telegram extends ProductType
{
    protected static function properties(): ShapeDecoder
    {
        return shape(
            telegramId: string()->constrained(
                minLength(is: 2),
                maxLength(is: 10),
            ),
            owner: Owner::type(),
        );
    }
}
