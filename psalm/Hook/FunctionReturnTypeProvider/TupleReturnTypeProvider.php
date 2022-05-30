<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider;

use Klimick\PsalmDecode\Helper\NamedArgumentsMapper;
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
        $source = $event->getStatementsSource();

        $mapped = NamedArgumentsMapper::map(
            $event->getCallArgs(),
            $source->getNodeTypeProvider()
        );

        return $mapped->get();
    }
}
