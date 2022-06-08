<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Common;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\ShapeDecoder;
use Klimick\Decode\Decoder\TaggedUnionDecoder;
use Klimick\Decode\Decoder\UnionDecoder;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Union;
use function array_is_list;
use function Fp\Collection\every;
use function Fp\Collection\first;
use function Fp\Collection\map;
use function Fp\Evidence\proveString;
use function in_array;

final class DecoderType
{
    /**
     * @no-named-arguments
     */
    public static function create(string $type, Union|Atomic $fst_param, Union|Atomic ...$rest): Union
    {
        $types = map([$fst_param, ...$rest], fn(Union|Atomic $t) => $t instanceof Atomic ? new Union([$t]) : $t);

        return new Union([
            new TGenericObject($type, $types),
        ]);
    }

    /**
     * @param non-empty-array<int|string, Union> $properties
     */
    public static function createShape(array $properties): Union
    {
        $shape = new TKeyedArray($properties);
        $shape->is_list = array_is_list($properties);
        $shape->sealed = every($properties, fn(Union $type) => !$type->possibly_undefined);

        return self::create(ShapeDecoder::class, $shape);
    }

    /**
     * @return Option<Union>
     */
    public static function getGeneric(Union $decoder_type): Option
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
     * @return Option<non-empty-array<string, Union>>
     */
    public static function getShapeProperties(Union $shape_decoder_type): Option
    {
        return self::getGeneric($shape_decoder_type)
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
