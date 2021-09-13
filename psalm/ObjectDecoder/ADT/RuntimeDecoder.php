<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\ADT;

use Klimick\Decode\Decoder\SumCases;
use ReflectionMethod;
use Psalm\Type;
use Klimick\PsalmDecode\Psalm;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\SumType;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Decoder\ProductType;
use Fp\Functional\Option\Option;

final class RuntimeDecoder
{
    /**
     * @return Option<ObjectDecoder>
     */
    public static function getDecoderFromRuntime(string $class_string): Option
    {
        return Option::some($class_string)
            ->filter(fn($class_string) => is_a($class_string, ProductType::class, true))
            ->flatMap(fn($class_string) => Option::try(fn() => $class_string::type()));
    }

    /**
     * @param string $class_string
     * @return Option<non-empty-array<string, Type\Union>>
     */
    public static function getProperties(string $class_string): Option
    {
        $parsed_type = self::getDecoderFromRuntime($class_string)
            ->map(fn($object_decoder) => $object_decoder->shape->name())
            ->flatMap(fn($typename) => Option::try(fn() => Type::parseString($typename)))
            ->flatMap(fn($parsed) => Psalm::asSingleAtomic($parsed))
            ->filter(fn($atomic) => $atomic instanceof Type\Atomic\TKeyedArray);

        return $parsed_type->map(function($keyed_array) {
            $mapped = [];

            foreach ($keyed_array->properties as $key => $type) {
                $mapped[(string)$key] = $type;
            }

            return $mapped;
        });
    }

    /**
     * @param class-string<SumType> $union_runtime_data_class
     * @return Option<non-empty-array<string, Type\Union>>
     */
    public static function getUnionCases(string $union_runtime_data_class): Option
    {
        return Option::do(function() use ($union_runtime_data_class) {
            $cases = yield self::getCasesWithReflection($union_runtime_data_class);
            $required_args = [];

            foreach ($cases as $case => $decoder) {
                $required_args[$case] = yield Option::try(
                    fn() => Type::parseString($decoder->name())
                );
            }

            return $required_args;
        });
    }

    /**
     * @param class-string<SumType> $union_runtime_data_class
     * @return Option<non-empty-array<string, DecoderInterface>>
     */
    private static function getCasesWithReflection(string $union_runtime_data_class): Option
    {
        $definition = Option::try(function() use ($union_runtime_data_class): mixed {
            $ref = new ReflectionMethod("{$union_runtime_data_class}::definition");
            $ref->setAccessible(true);

            return $ref->invoke(null);
        });

        return $definition
            ->filter(fn($result) => $result instanceof SumCases)
            ->map(fn($result) => $result->cases);
    }
}
