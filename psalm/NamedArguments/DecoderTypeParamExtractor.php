<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\NamedArguments;

use Psalm\Type;
use Psalm\Codebase;
use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder;
use function Fp\Cast\asList;
use function Fp\Collection\at;
use function Fp\Collection\first;
use function Fp\Collection\firstOf;
use function Fp\Collection\flatMap;
use function Fp\Collection\second;
use function Fp\Evidence\proveNonEmptyString;
use function Fp\Evidence\proveTrue;

final class DecoderTypeParamExtractor
{
    /**
     * @return Option<Type\Union>
     */
    public static function extract(Type\Union $named_arg_type, Codebase $codebase): Option
    {
        return Option::do(function() use ($named_arg_type, $codebase) {
            $atomics = asList($named_arg_type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $decoder = yield first($atomics);

            return yield match (true) {
                ($decoder instanceof Type\Atomic\TLiteralString) => self::fromStringCallable($decoder, $codebase),
                ($decoder instanceof Type\Atomic\TKeyedArray) => self::fromArrayCallable($decoder, $codebase),
                ($decoder instanceof Type\Atomic\TCallable) => self::fromCallable($decoder),
                ($decoder instanceof Type\Atomic\TClosure) => self::fromClosure($decoder),
                default => self::fromPlainDecoder($named_arg_type),
            };
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function fromPlainDecoder(Type\Union $type): Option
    {
        return Option::do(function() use ($type) {
            $atomics = asList($type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $generic_object = yield firstOf($atomics, Type\Atomic\TGenericObject::class);

            yield proveTrue($generic_object->value === Decoder::class);
            yield proveTrue(1 === count($generic_object->type_params));

            return yield first($generic_object->type_params);
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function fromStringCallable(
        Type\Atomic\TLiteralString $callable,
        Codebase $codebase,
    ): Option
    {
        return Option::do(function() use ($callable, $codebase) {
            $callable_id = yield proveNonEmptyString($callable->value)
                ->map(fn($id) => strtolower($id));

            $decoder_from_callable = yield match (true) {
                str_contains($callable_id, '::') => self::fromStaticMethod($callable_id, $codebase),
                default => self::fromFunction($codebase, $callable_id),
            };

            return yield self::fromPlainDecoder($decoder_from_callable);
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function fromArrayCallable(
        Type\Atomic\TKeyedArray $callable,
        Codebase $codebase,
    ): Option
    {
        return Option::do(function() use ($callable, $codebase) {
            yield proveTrue($callable->is_list && 2 === count($callable->properties));

            $properties = flatMap(
                $callable->properties,
                fn(Type\Union $t) => asList($t->getAtomicTypes()),
            );

            $class = yield firstOf($properties, Type\Atomic\TLiteralClassString::class, invariant: true);
            $method = yield firstOf($properties, Type\Atomic\TLiteralString::class, invariant: true);

            return yield self::fromStringCallable(
                new Type\Atomic\TLiteralString("{$class->value}::$method->value"), $codebase
            );
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function fromClosure(Type\Atomic\TClosure $closure): Option
    {
        return Option::of($closure->return_type)
            ->flatMap(fn(Type\Union $return_type) => self::fromPlainDecoder($return_type));
    }

    /**
     * @return Option<Type\Union>
     */
    private static function fromCallable(Type\Atomic\TCallable $closure): Option
    {
        return Option::of($closure->return_type)
            ->flatMap(fn(Type\Union $return_type) => self::fromPlainDecoder($return_type));
    }

    /**
     * @return Option<Type\Union>
     */
    private static function fromStaticMethod(string $function_id, Codebase $codebase): Option
    {
        return Option::do(function() use ($function_id, $codebase) {
            $class_method = explode('::', $function_id);

            $class = yield first($class_method);
            $method = yield second($class_method);

            $class_storage = yield Option::try(fn() => $codebase->classlike_storage_provider->get($class));
            $method_storage = yield at($class_storage->methods, $method);

            return yield Option::of($method_storage->return_type);
        });
    }

    /**
     * @psalm-param non-empty-lowercase-string $function_id
     * @return Option<Type\Union>
     */
    private static function fromFunction(
        Codebase $codebase,
        string $function_id,
    ): Option
    {
        return Option::try(
            fn() => $codebase->functions
                ->getStorage(null, $function_id)
                ->return_type
        );
    }
}
