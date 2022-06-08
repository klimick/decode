<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Common;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use PhpParser\Node;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\Type\Union;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TObjectWithProperties;
use function array_key_exists;
use function Fp\Collection\at;
use function Fp\Evidence\proveString;

final class NamedArgumentsMapper
{
    /**
     * @param non-empty-list<Node\Arg> $call_args
     * @return Union
     */
    public static function map(StatementsSource $source, array $call_args): Union
    {
        $properties = [];

        foreach ($call_args as $offset => $arg) {
            $arg_type = PsalmApi::$types
                ->getType($source, $arg->value)
                ->getOrElse(Type::getMixed());

            $property = Option::fromNullable($arg->name)
                ->flatMap(fn($id) => proveString($id->name))
                ->getOrElse($offset);

            $type = DecoderType::getGeneric($arg_type)
                ->map(fn($type) => self::isOptional($arg_type)
                    ? PsalmApi::$types->asPossiblyUndefined($type)
                    : $type)
                ->getOrElse(Type::getMixed());

            $properties[$property] = $type;
        }

        return DecoderType::createShape($properties);
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
