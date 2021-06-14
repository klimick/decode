<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ShapeDecoder;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\NamedArguments\DecoderTypeParamExtractor;
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
        $inferred = Option
            ::do(static function() use ($event) {
                $args = yield proveNonEmptyList($event->getCallArgs());

                $types = [];
                $source = $event->getStatementsSource();

                foreach ($args as $arg) {
                    $arg_type = yield Option::fromNullable($source->getNodeTypeProvider()->getType($arg->value));
                    $types[] = yield DecoderTypeParamExtractor::extract($arg_type, $source->getCodebase());
                }

                return new Type\Atomic\TKeyedArray($types);
            })
            ->get() ?? DecoderType::createEmptyArray();

        return new Type\Union([$inferred]);
    }
}
