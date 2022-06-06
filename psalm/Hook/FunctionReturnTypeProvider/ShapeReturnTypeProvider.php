<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider;

use Klimick\PsalmDecode\Common\DecoderType;
use Klimick\PsalmDecode\Common\NamedArgumentsMapper;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type;

final class ShapeReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return ['klimick\decode\decoder\shape', 'klimick\decode\decoder\partialshape'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        $mapped = NamedArgumentsMapper::map(
            call_args: $event->getCallArgs(),
            source: $event->getStatementsSource(),
            partial: 'klimick\decode\decoder\partialshape' === $event->getFunctionId(),
        );

        return $mapped
            ->flatMap(fn($type) => DecoderType::withShapeDecoderIntersection($type))
            ->get();
    }
}
