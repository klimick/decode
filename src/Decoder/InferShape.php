<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\Shape\ShapeDecoder;

/**
 * @template T of object
 */
interface InferShape
{
    /**
     * @return ShapeDecoder<array<string, mixed>>
     */
    public static function shape(): ShapeDecoder;
}
