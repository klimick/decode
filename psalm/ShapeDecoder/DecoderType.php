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
                ? new Type\Atomic\TArray([Type::getNever(), Type::getNever()])
                : new Type\Atomic\TKeyedArray($properties)
        ]);

        return new Type\Union([
            new Type\Atomic\TGenericObject(DecoderInterface::class, [$properties_atomic]),
        ]);
    }

    public static function withTypeParameter(Type\Union $type_parameter): Type\Union
    {
        return new Type\Union([
            new Type\Atomic\TGenericObject(DecoderInterface::class, [$type_parameter]),
        ]);
    }
}
