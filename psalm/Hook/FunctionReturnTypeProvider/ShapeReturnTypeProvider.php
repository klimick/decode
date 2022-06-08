<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider;

use Klimick\PsalmDecode\Common\DecoderType;
use Klimick\PsalmDecode\Common\NamedArgumentsMapper;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type;
use function Fp\Evidence\proveNonEmptyList;

final class ShapeReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return ['klimick\decode\decoder\shape'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        return proveNonEmptyList($event->getCallArgs())
            ->map(fn($args) => NamedArgumentsMapper::map($event->getStatementsSource(), $args))
            ->flatMap(fn($type) => DecoderType::withShapeDecoderIntersection($type))
            ->get();
    }
}
