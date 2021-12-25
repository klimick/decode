<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Psalm\Type;

final class DecoderType
{
    /**
     * @param array<int|string, Type\Union> $properties
     */
    public static function createShape(array $properties): Type\Atomic\TArray|Type\Atomic\TKeyedArray
    {
        if (empty($properties)) {
            return new Type\Atomic\TArray([
                Type::getNever(),
                Type::getNever(),
            ]);
        }

        return new Type\Atomic\TKeyedArray($properties);
    }
}
