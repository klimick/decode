<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider;

use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\PsalmDecode\Common\NamedArgumentsMapper;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type;

final class ShapeFunctionReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return ['klimick\decode\decoder\shape'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        return PsalmApi::$args->getNonEmptyCallArgs($event)
            ->map(fn($args) => NamedArgumentsMapper::namedArgsToArray($args->toArray()))
            ->flatMap(fn($decoders) => NamedArgumentsMapper::mapDecoders($decoders))
            ->get();
    }
}
