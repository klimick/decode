<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\NamedArguments;

use Klimick\PsalmDecode\Psalm;
use PhpParser\Node;
use Psalm\Type;
use Psalm\NodeTypeProvider;
use Fp\Collections\ArrayList;
use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\ShapeDecoder\DecoderType;
use function Fp\Evidence\proveString;

final class NamedArgumentsMapper
{
    /**
     * @param list<Node\Arg> $call_args
     * @return Option<Type\Union>
     */
    public static function map(array $call_args, NodeTypeProvider $provider, bool $partial = false): Option
    {
        return ArrayList::collect($call_args)
            ->everyMap(fn($arg) => self::getPropertyInfo($arg, $provider, $partial))
            ->flatMap(
                fn($properties) => $properties
                    ->toHashMap(fn($info) => [$info['property'], $info['type']])
                    ->toAssocArray()
            )
            ->map(fn($properties) => DecoderType::createShape($properties));
    }

    /**
     * @return Option<array{property: string, type: Type\Union}>
     */
    private static function getPropertyInfo(Node\Arg $named_arg, NodeTypeProvider $provider, bool $partial = false): Option
    {
        return Option::do(function() use ($named_arg, $provider, $partial) {
            $named_arg_type = yield Option::fromNullable($provider->getType($named_arg->value));
            $arg_identifier = yield Option::fromNullable($named_arg->name);

            return [
                'property' => yield proveString($arg_identifier->name),
                'type' => yield DecoderTypeParamExtractor::extract($named_arg_type)
                    ->map(fn($type) => $partial ? self::asPossiblyUndefined($type) : $type),
            ];
        });
    }

    private static function asPossiblyUndefined(Type\Union $union): Type\Union
    {
        $cloned = clone $union;
        $cloned->possibly_undefined = true;

        return $cloned;
    }
}
