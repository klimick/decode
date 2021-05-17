<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use Psalm\Type;
use Fp\Functional\Option\Option;
use Klimick\Decode\DecoderInterface;
use function Fp\Cast\asList;
use function Fp\Collection\first;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveTrue;

final class ShapePropertiesExtractor
{
    /**
     * @param Type\Union $shape_decoder_type
     * @return Option<array<string, Type\Union>>
     */
    public static function fromDecoder(Type\Union $shape_decoder_type): Option
    {
        return Option::do(function() use ($shape_decoder_type) {
            $atomics = asList($shape_decoder_type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $decoder = yield firstOf($atomics, Type\Atomic\TGenericObject::class);
            yield proveTrue(DecoderInterface::class === $decoder->value);
            yield proveTrue(1 === count($decoder->type_params));

            $decoder_type_param = yield first($decoder->type_params);

            return yield self::fromDecoderTypeParam($decoder_type_param);
        });
    }

    /**
     * @return Option<array<string, Type\Union>>
     */
    public static function fromDecoderTypeParam(Type\Union $decoder_type_param): Option
    {
        return Option::do(function() use ($decoder_type_param) {
            $type_param_atomic = $decoder_type_param->getAtomicTypes();
            yield proveTrue(1 === count($type_param_atomic));

            $keyed_array = yield first($type_param_atomic);

            $extracted_shape = match (true) {
                ($keyed_array instanceof Type\Atomic\TArray) => [],
                ($keyed_array instanceof Type\Atomic\TKeyedArray) => self::remapKeys($keyed_array)
            };

            return yield Option::of($extracted_shape);
        });
    }

    /**
     * @return array<string, Type\Union>
     */
    private static function remapKeys(Type\Atomic\TKeyedArray $keyed_array): array
    {
        $properties = [];

        foreach ($keyed_array->properties as $property => $type) {
            $properties[(string) $property] = $type;
        }

        return $properties;
    }
}
