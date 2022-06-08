<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Common;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use PhpParser\Node;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TObjectWithProperties;
use function array_key_exists;
use function Fp\Collection\at;
use function Fp\Evidence\proveString;

final class NamedArgumentsMapper
{
    /**
     * @param list<Node\Arg> $call_args
     * @return Option<Type\Union>
     */
    public static function map(array $call_args, StatementsSource $source, bool $partial = false): Option
    {
        return Option::do(function() use ($call_args, $source, $partial) {
            $properties = [];

            foreach ($call_args as $offset => $arg) {
                $info = yield self::getPropertyInfo($offset, $arg, $source, $partial);
                $properties[$info['property']] = $info['type'];
            }

            return DecoderType::createShapeDecoder($properties);
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
    private static function getPropertyInfo(int $arg_offset, Node\Arg $arg, StatementsSource $source, bool $partial = false): Option
    {
        return Option::do(function() use ($arg_offset, $arg, $source, $partial) {
            $arg_type = yield PsalmApi::$types->getType($source, $arg->value);

            return [
                'property' => self::getArgId($arg_offset, $arg),
                'type' => yield DecoderType::getDecoderGeneric($arg_type)
                    ->map(fn($type) => ($partial || self::isOptional($arg_type))
                        ? PsalmApi::$types->asPossiblyUndefined($type)
                        : $type),
            ];
        });
    }

    private static function isOptional(Type\Union $union): bool
    {
        return PsalmApi::$types
            ->asSingleAtomicOf(Type\Atomic\TNamedObject::class, $union)
            ->map(fn(TNamedObject $named_object) => $named_object->getIntersectionTypes() ?? [])
            ->flatMap(fn(array $intersections) => at($intersections, 'object')->filterOf(TObjectWithProperties::class))
            ->map(fn(TObjectWithProperties $object) => array_key_exists('possiblyUndefined', $object->properties))
            ->getOrElse(false);
    }
}
