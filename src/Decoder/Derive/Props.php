<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\Derive;

use Klimick\Decode\Internal\Shape\ShapeDecoder;

/**
 * @template T of object
 */
interface Props
{
    /**
     * @return ShapeDecoder<array<string, mixed>>
     */
    public static function props(): ShapeDecoder;
}
