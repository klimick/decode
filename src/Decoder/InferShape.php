<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

interface InferShape
{
    public static function shape(): ShapeDecoder;
}
