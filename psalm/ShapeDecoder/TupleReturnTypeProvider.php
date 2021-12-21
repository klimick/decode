<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\NamedArguments\DecoderTypeParamExtractor;
use Klimick\PsalmTest\Integration\CallArg;
use Klimick\PsalmTest\Integration\Psalm;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type;

final class TupleReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return ['klimick\decode\decoder\tuple'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        $to_decoder_type_param = fn(CallArg $arg): Option => $arg
            ->flatMap(fn(Type\Union $type) => DecoderTypeParamExtractor::extract($type));

        return Psalm::getNonEmptyCallArgs($event)
            ->flatMap(fn($args) => $args->everyMap($to_decoder_type_param))
            ->map(fn($args) => $args->map(fn($call_arg) => $call_arg->type)->toArray())
            ->map(function($types) {
                $tuple = new Type\Atomic\TKeyedArray($types);
                $tuple->is_list = true;

                return DecoderType::withTypeParameter(new Type\Union([$tuple]));
            })
            ->get();
    }
}
