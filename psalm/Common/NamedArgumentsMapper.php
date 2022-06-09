<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Common;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\CallArg;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Psalm\Type\Union;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TObjectWithProperties;
use function array_key_exists;
use function Fp\Collection\at;
use function Fp\Collection\everyMap;
use function Fp\Evidence\proveString;

final class NamedArgumentsMapper
{
    /**
     * @param non-empty-list<CallArg> $call_args
     * @return non-empty-array<int|string, Union>
     */
    public static function namedArgsToArray(array $call_args): array
    {
        $properties = [];

        foreach ($call_args as $offset => $arg) {
            $property = Option::fromNullable($arg->node->name)
                ->flatMap(fn($id) => proveString($id->name))
                ->getOrElse($offset);

            $properties[$property] = $arg->type;
        }

        return $properties;
    }

    /**
     * @param non-empty-array<int|string, Union> $decoder_types
     * @return Option<Union>
     */
    public static function mapDecoders(array $decoder_types): Option
    {
        return everyMap($decoder_types, fn($type) => DecoderType::getGeneric($type)
            ->map(fn($generic) => self::isOptional($type)
                ? PsalmApi::$types->asPossiblyUndefined($generic)
                : $generic))
            ->map(fn($properties) => DecoderType::createShape($properties));
    }

    private static function isOptional(Union $union): bool
    {
        return PsalmApi::$types
            ->asSingleAtomicOf(TNamedObject::class, $union)
            ->map(fn(TNamedObject $named_object) => $named_object->getIntersectionTypes() ?? [])
            ->flatMap(fn(array $intersections) => at($intersections, 'object')->filterOf(TObjectWithProperties::class))
            ->map(fn(TObjectWithProperties $object) => array_key_exists('possiblyUndefined', $object->properties))
            ->getOrElse(false);
    }
}
