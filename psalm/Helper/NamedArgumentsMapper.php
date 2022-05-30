<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Fp\Functional\Option\Option;
use Klimick\Decode\HighOrder\Brand\OptionalBrand;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use PhpParser\Node;
use Psalm\NodeTypeProvider;
use Psalm\Type;
use function Fp\Evidence\proveString;

final class NamedArgumentsMapper
{
    /**
     * @param list<Node\Arg> $call_args
     * @return Option<Type\Union>
     */
    public static function map(array $call_args, NodeTypeProvider $provider, bool $partial = false): Option
    {
        return Option::do(function() use ($call_args, $provider, $partial) {
            $properties = [];

            foreach ($call_args as $offset => $arg) {
                $info = yield self::getPropertyInfo($offset, $arg, $provider, $partial);
                $properties[$info['property']] = $info['type'];
            }

            return DecoderType::createShape($properties);
        });
    }

    private static function getArgId(int $arg_offset, Node\Arg $named_arg): string|int
    {
        return Option::fromNullable($named_arg->name)
            ->flatMap(fn($id) => proveString($id->name))
            ->getOrElse($arg_offset);
    }

    /**
     * @return Option<array{property: int|string, type: Type\Union}>
     */
    private static function getPropertyInfo(int $arg_offset, Node\Arg $arg, NodeTypeProvider $provider, bool $partial = false): Option
    {
        return Option::do(function() use ($arg_offset, $arg, $provider, $partial) {
            $arg_type = yield Option::fromNullable($provider->getType($arg->value));

            return [
                'property' => self::getArgId($arg_offset, $arg),
                'type' => yield DecoderType::extractTypeParam($arg_type)
                    ->map(fn($type) => ($partial || self::isOptional($arg_type))
                        ? self::asPossiblyUndefined($type)
                        : $type),
            ];
        });
    }

    private static function isOptional(Type\Union $union): bool
    {
        return PsalmApi::$types->asSingleAtomicOf(Type\Atomic\TNamedObject::class, $union)
            ->map(fn($named_object) => $named_object->getIntersectionTypes() ?? [])
            ->map(fn($intersections) => array_key_exists(OptionalBrand::class, $intersections))
            ->getOrElse(false);
    }

    private static function asPossiblyUndefined(Type\Union $union): Type\Union
    {
        $cloned = clone $union;
        $cloned->possibly_undefined = true;

        return $cloned;
    }
}
