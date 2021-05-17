<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use Klimick\PsalmDecode\NamedArguments\NamedArgumentsMapper;
use SimpleXMLElement;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use Psalm\Type;

final class ShapeReturnTypeProvider implements FunctionReturnTypeProviderInterface, PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $registration->registerHooksFromClass(self::class);
    }

    public static function getFunctionIds(): array
    {
        return ['klimick\decode\shape', 'klimick\decode\partialshape'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        $source = $event->getStatementsSource();
        $is_partial_shape = 'klimick\decode\partialshape' === $event->getFunctionId();

        $mapped = NamedArgumentsMapper::map(
            call_args: $event->getCallArgs(),
            source: $source,
            provider: $source->getNodeTypeProvider(),
            codebase: $source->getCodebase(),
            partial: $is_partial_shape,
        );

        return $mapped->get();
    }
}
