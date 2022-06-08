<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider;

use Fp\PsalmToolkit\Toolkit\CallArg;
use Klimick\PsalmDecode\Common\DecoderType;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type;

final class IntersectionReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return ['klimick\decode\decoder\intersection'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        return PsalmApi::$args->getNonEmptyCallArgs($event)
            ->flatMap(fn($args) => $args->everyMap(fn(CallArg $arg) => DecoderType::getShapeProperties($arg->type)))
            ->map(fn($shapes) => array_merge(...$shapes->toArray()))
            ->map(fn($shapes) => DecoderType::createShape($shapes))
            ->get();
    }
}
