<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\PsalmTest\Integration\Psalm;
use Psalm\Type;
use function Fp\Collection\first;
use function Fp\Collection\reindex;

final class ShapePropertiesExtractor
{
    /**
     * @param Type\Union $shape_decoder_type
     * @return Option<array<string, Type\Union>>
     */
    public static function fromDecoder(Type\Union $shape_decoder_type): Option
    {
        return Psalm::asSingleAtomicOf(Type\Atomic\TGenericObject::class, $shape_decoder_type)
            ->filter(fn($generic) => $generic->value === DecoderInterface::class)
            ->flatMap(fn($generic) => first($generic->type_params))
            ->flatMap(fn($type_param) => self::fromDecoderTypeParam($type_param));
    }

    /**
     * @return Option<array<string, Type\Union>>
     */
    public static function fromDecoderTypeParam(Type\Union $decoder_type_param): Option
    {
        return Psalm::asSingleAtomic($decoder_type_param)->flatMap(
            fn($keyed_array) => Option::fromNullable(match (true) {
                ($keyed_array instanceof Type\Atomic\TArray) => [],
                ($keyed_array instanceof Type\Atomic\TKeyedArray) => self::remapKeys($keyed_array),
                default => null,
            })
        );
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
