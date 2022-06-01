<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Psalm\Type;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Union;
use function Fp\Collection\first;
use function Fp\Collection\reindex;

final class DecoderType
{
    /**
     * @param array<int|string, Type\Union> $properties
     */
    public static function createShapeDecoder(array $properties): Type\Union
    {
        return new Type\Union([
            new Type\Atomic\TGenericObject(DecoderInterface::class, [
                new Type\Union([
                    empty($properties)
                        ? new Type\Atomic\TArray([Type::getNever(), Type::getNever()])
                        : new Type\Atomic\TKeyedArray($properties),
                ]),
            ]),
        ]);
    }

    /**
     * @return Option<Union>
     */
    public static function getDecoderGeneric(Union $decoder_type): Option
    {
        return PsalmApi::$types->asSingleAtomicOf(TGenericObject::class, $decoder_type)
            ->filter(fn(TGenericObject $generic) => $generic->value === DecoderInterface::class)
            ->flatMap(fn(TGenericObject $type) => first($type->type_params));
    }

    /**
     * @param Type\Union $shape_decoder_type
     * @return Option<array<string, Type\Union>>
     */
    public static function getShapeProperties(Type\Union $shape_decoder_type): Option
    {
        return self::getDecoderGeneric($shape_decoder_type)
            ->flatMap(fn($decoder_type_param) => PsalmApi::$types->asSingleAtomic($decoder_type_param)->flatMap(
                fn($keyed_array) => Option::fromNullable(match (true) {
                    ($keyed_array instanceof Type\Atomic\TArray) => [],
                    ($keyed_array instanceof Type\Atomic\TKeyedArray) => self::remapKeys($keyed_array),
                    default => null,
                })
            ));
    }

    /**
     * @return array<string, Type\Union>
     */
    private static function remapKeys(Type\Atomic\TKeyedArray $keyed_array): array
    {
        return reindex(
            $keyed_array->properties,
            fn(Type\Union $_, int|string $property) => (string)$property,
        );
    }
}
