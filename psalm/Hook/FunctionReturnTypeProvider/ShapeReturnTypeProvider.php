<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
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
        $type = Option::do(function() use ($event) {
            $mapped = yield NamedArgumentsMapper::map(
                call_args: $event->getCallArgs(),
                source: $event->getStatementsSource(),
                partial: 'klimick\decode\decoder\partialshape' === $event->getFunctionId(),
            );

            $decoder = yield PsalmApi::$types->asSingleAtomicOf(Type\Atomic\TGenericObject::class, $mapped);
            $shape = yield PsalmApi::$types->getFirstGeneric($decoder, DecoderInterface::class);

            $decoder->addIntersectionType(
                new Type\Atomic\TGenericObject(ShapeDecoder::class, [$shape]),
            );

            return new Type\Union([$decoder]);
        });

        return $type->get();
    }
}
