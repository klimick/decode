<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use Psalm\Type;
use Klimick\Decode\DecoderInterface;

final class ShapeDecoderType
{
    /**
     * @param array<string, Type\Union> $properties
     * @return Type\Union
     */
    public static function create(array $properties): Type\Union
    {
        $properties_atomic = new Type\Union([
            empty($properties)
                ? new Type\Atomic\TArray([Type::getEmpty(), Type::getEmpty()])
                : new Type\Atomic\TKeyedArray($properties)
        ]);

        $decoder_atomic = new Type\Atomic\TGenericObject(DecoderInterface::class, [$properties_atomic]);

        return new Type\Union([$decoder_atomic]);
    }
}
