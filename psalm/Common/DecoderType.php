<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Common;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\ShapeDecoder;
use Klimick\Decode\Decoder\TaggedUnionDecoder;
use Klimick\Decode\Decoder\UnionDecoder;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use function array_is_list;
use function Fp\Collection\every;
use function Fp\Collection\first;
use function Fp\Evidence\proveString;
use function in_array;

final class DecoderType
{
    /**
     * @param non-empty-array<int|string, Union> $properties
     */
    public static function createShapeDecoder(array $properties): Union
    {
        $shape = new TKeyedArray($properties);
        $shape->is_list = array_is_list($properties);
        $shape->sealed = every($properties, fn(Union $type) => !$type->possibly_undefined);

        return new Union([
            new TGenericObject(ShapeDecoder::class, [
                new Union([$shape]),
            ]),
        ]);
    }

    /**
     * @return Option<Union>
     */
    public static function getDecoderGeneric(Union $decoder_type): Option
    {
        return PsalmApi::$types->asSingleAtomicOf(TGenericObject::class, $decoder_type)
            ->filter(fn(TGenericObject $generic) => in_array($generic->value, [
                DecoderInterface::class,
                ShapeDecoder::class,
                UnionDecoder::class,
                TaggedUnionDecoder::class,
            ]))
            ->flatMap(fn(TGenericObject $type) => first($type->type_params));
    }

    /**
     * @param Union $shape_decoder_type
     * @return Option<non-empty-array<string, Union>>
     */
    public static function getShapeProperties(Union $shape_decoder_type): Option
    {
        return self::getDecoderGeneric($shape_decoder_type)
            ->flatMap(fn($generic) => PsalmApi::$types->asSingleAtomicOf(TKeyedArray::class, $generic))
            ->flatMap(fn($shape) => self::remapKeys($shape));
    }

    /**
     * @return Option<non-empty-array<string, Union>>
     */
    private static function remapKeys(TKeyedArray $keyed_array): Option
    {
        return Option::do(function() use ($keyed_array) {
            $remapped = [];

            foreach ($keyed_array->properties as $property => $type) {
                $asString = yield proveString($property);
                $remapped[$asString] = $type;
            }

            return $remapped;
        });
    }
}
