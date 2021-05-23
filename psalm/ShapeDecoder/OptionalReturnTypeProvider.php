<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\NamedArguments\DecoderTypeParamExtractor;
use Psalm\Type;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use function Fp\Evidence\proveTrue;

final class OptionalReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return ['klimick\decode\optional'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        $return_type = Option::do(function() use ($event) {
            $source = $event->getStatementsSource();
            $provider = $source->getNodeTypeProvider();
            $codebase = $source->getCodebase();

            $call_args = $event->getCallArgs();
            yield proveTrue(1 === count($call_args));

            $type = yield Option::of($provider->getType($call_args[0]->value))
                ->flatMap(fn($type) => DecoderTypeParamExtractor::extract($type, $codebase));

            $optional_type = clone $type;
            $optional_type->possibly_undefined = true;

            return DecoderType::withTypeParameter($optional_type);
        });

        return $return_type->get();
    }
}
