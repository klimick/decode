<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Psalm\Type;
use Klimick\Decode\DecoderInterface;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\RuntimeData;
use Fp\Functional\Option\Option;
use function Fp\Cast\asList;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class RuntimeDecoder
{
    /**
     * @return Option<DecoderInterface>
     */
    public static function instance(string $class_string): Option
    {
        return Option::do(function() use ($class_string) {
            yield proveTrue(is_a($class_string, RuntimeData::class, true));

            return yield Option::try(fn() => $class_string::definition());
        });
    }

    /**
     * @param string $class_string
     * @return Option<array<array-key, Type\Union>>
     */
    public static function getProperties(string $class_string): Option
    {
        return Option::do(function() use ($class_string) {
            $decoder = yield self::instance($class_string);

            $shape_type = yield proveOf($decoder, ObjectDecoder::class)
                ->map(fn($object_decoder) => $object_decoder->shape->name())
                ->flatMap(fn($type) => Option::try(fn() => Type::parseString($type)));

            $atomics = asList($shape_type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            return yield firstOf($atomics, Type\Atomic\TKeyedArray::class)
                ->map(fn($keyed_array) => $keyed_array->properties);
        });
    }
}
