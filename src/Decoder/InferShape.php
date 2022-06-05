<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\Shape\ShapeDecoder;

interface InferShape
{
    /**
     * @return ShapeDecoder<array<string, mixed>>
     */
    public static function shape(): ShapeDecoder;
}
