<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\NamedArguments\DecoderTypeParamExtractor;
use Klimick\PsalmDecode\Psalm;
use Psalm\Plugin\EventHandler\Event\FunctionReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\FunctionReturnTypeProviderInterface;
use Psalm\Type;
use function Fp\Evidence\proveNonEmptyList;

final class TupleReturnTypeProvider implements FunctionReturnTypeProviderInterface
{
    public static function getFunctionIds(): array
    {
        return ['klimick\decode\decoder\tuple'];
    }

    public static function getFunctionReturnType(FunctionReturnTypeProviderEvent $event): ?Type\Union
    {
        $inferred = Option::do(static function() use ($event) {
            $args = yield proveNonEmptyList($event->getCallArgs());

            $source = $event->getStatementsSource();
            $type_provider = $source->getNodeTypeProvider();

            $types = [];

            foreach ($args as $arg) {
                $type = yield Psalm::getType($type_provider, $arg->value);
                $types[] = yield DecoderTypeParamExtractor::extract($type, $source->getCodebase());
            }

            $tuple = new Type\Atomic\TKeyedArray($types);
            $tuple->is_list = true;

            return DecoderType::withTypeParameter(
                new Type\Union([$tuple])
            );
        });

        return $inferred->get();
    }
}
