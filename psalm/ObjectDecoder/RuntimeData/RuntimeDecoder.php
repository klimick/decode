<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Klimick\PsalmDecode\Psalm;
use Psalm\Type;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Decoder\RuntimeData;
use Fp\Functional\Option\Option;

final class RuntimeDecoder
{
    /**
     * @return Option<ObjectDecoder>
     */
    public static function getDecoderTypeFromRuntime(string $class_string): Option
    {
        return Option::some($class_string)
            ->filter(fn($class_string) => is_a($class_string, RuntimeData::class, true))
            ->flatMap(fn($class_string) => Option::try(fn() => $class_string::type()));
    }

    /**
     * @param string $class_string
     * @return Option<array<array-key, Type\Union>>
     */
    public static function getProperties(string $class_string): Option
    {
        return self::getDecoderTypeFromRuntime($class_string)
            ->map(fn($object_decoder) => $object_decoder->shape->name())
            ->flatMap(fn($typename) => Option::try(fn() => Type::parseString($typename)))
            ->flatMap(fn($shape_type) => Psalm::asSingleAtomic($shape_type))
            ->filter(fn($atomic) => $atomic instanceof Type\Atomic\TKeyedArray)
            ->map(fn($keyed_array) => $keyed_array->properties);
    }
}
