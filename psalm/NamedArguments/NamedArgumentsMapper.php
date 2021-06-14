<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\NamedArguments;

use Klimick\PsalmDecode\Psalm;
use PhpParser\Node;
use Psalm\Type;
use Psalm\Codebase;
use Psalm\NodeTypeProvider;
use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\ShapeDecoder\DecoderType;
use function Fp\Evidence\proveString;

final class NamedArgumentsMapper
{
    /**
     * @param list<Node\Arg> $call_args
     * @return Option<Type\Union>
     */
    public static function map(
        array $call_args,
        NodeTypeProvider $provider,
        Codebase $codebase,
        bool $partial = false,
    ): Option
    {
        return Option::do(function() use ($call_args, $provider, $codebase, $partial) {
            $properties = [];

            foreach ($call_args as $arg) {
                $info = yield self::getPropertyInfo($arg, $provider, $codebase);

                $properties[$info['property']] = $partial
                    ? self::asPossiblyUndefined($info['type'])
                    : $info['type'];
            }

            return DecoderType::createShape($properties);
        });
    }

    /**
     * @psalm-type PropertyInfo = array{
     *     property: string,
     *     type: Type\Union
     * }
     *
     * @return Option<PropertyInfo>
     */
    private static function getPropertyInfo(
        Node\Arg $named_arg,
        NodeTypeProvider $provider,
        Codebase $codebase,
    ): Option
    {
        return Option::do(function() use ($named_arg, $provider, $codebase) {
            $named_arg_type = yield Psalm::getType($provider, $named_arg->value);
            $arg_identifier = yield Option::fromNullable($named_arg->name);

            return [
                'property' => yield proveString($arg_identifier->name),
                'type' => yield DecoderTypeParamExtractor::extract($named_arg_type, $codebase),
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
