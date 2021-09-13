<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use Psalm\Type;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Decoder\DecoderInterface;

final class DecoderType
{
    /**
     * @param array<string, Type\Union> $properties
     */
    public static function createShape(array $properties): Type\Union
    {
        $properties_atomic = new Type\Union([
            empty($properties)
                ? new Type\Atomic\TArray([Type::getEmpty(), Type::getEmpty()])
                : new Type\Atomic\TKeyedArray($properties)
        ]);

        $decoder_atomic = new Type\Atomic\TGenericObject(DecoderInterface::class, [$properties_atomic]);
        $decoder_atomic->addIntersectionType(new Type\Atomic\TGenericObject(ShapeDecoder::class, [$properties_atomic]));

        return new Type\Union([$decoder_atomic]);
    }

    public static function withTypeParameter(Type\Union $type_parameter): Type\Union
    {
        return new Type\Union([
            new Type\Atomic\TGenericObject(DecoderInterface::class, [$type_parameter]),
        ]);
    }
}
