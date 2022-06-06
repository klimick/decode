<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

interface InferShape
{
    /**
     * @return ShapeDecoder<array<string, mixed>>
     */
    public static function shape(): ShapeDecoder;
}
