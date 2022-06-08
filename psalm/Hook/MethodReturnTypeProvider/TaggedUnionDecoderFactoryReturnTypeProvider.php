<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\MethodReturnTypeProvider;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\Factory\TaggedUnionDecoderFactory;
use Klimick\PsalmDecode\Issue;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type\Union;
use function Fp\Evidence\proveTrue;

final class TaggedUnionDecoderFactoryReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [TaggedUnionDecoderFactory::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Union
    {
        Option::do(function() use ($event) {
            $args = yield PsalmApi::$args->getCallArgs($event);

            yield proveTrue($args->count() >= 2)
                ->orElse(Issue\TooFewArgsForTaggedUnion::raise($event));

            yield proveTrue($args->every(fn($arg) => null !== $arg->node->name))
                ->orElse(Issue\NotNamedArgForTaggedUnion::raise($event));
        });

        return null;
    }
}
