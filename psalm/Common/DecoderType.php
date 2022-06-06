<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Common;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\ShapeDecoder;
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
     * @return Option<Type\Union>
     */
    public static function withShapeDecoderIntersection(Union $mapped): Option
    {
        return Option::do(function() use ($mapped) {
            $decoder = yield PsalmApi::$types->asSingleAtomicOf(Type\Atomic\TGenericObject::class, $mapped);
            $shape = yield PsalmApi::$types->getFirstGeneric($decoder, DecoderInterface::class);

            return new Type\Union([
                PsalmApi::$types->addIntersection(
                    to: $decoder,
                    type: new Type\Atomic\TGenericObject(ShapeDecoder::class, [$shape]),
                ),
            ]);
        });
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
