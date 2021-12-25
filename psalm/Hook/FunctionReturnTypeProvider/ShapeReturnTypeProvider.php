<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider;

use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\PsalmDecode\Helper\NamedArgumentsMapper;
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
        $source = $event->getStatementsSource();
        $is_partial_shape = 'klimick\decode\decoder\partialshape' === $event->getFunctionId();

        $mapped = NamedArgumentsMapper::map(
            call_args: $event->getCallArgs(),
            provider: $source->getNodeTypeProvider(),
            partial: $is_partial_shape,
        );

        return $mapped
            ->map(
                fn($properties) => new Type\Union([
                    new Type\Atomic\TGenericObject(ShapeDecoder::class, [
                        new Type\Union([$properties])
                    ]),
                ])
            )
            ->get();
    }
}
