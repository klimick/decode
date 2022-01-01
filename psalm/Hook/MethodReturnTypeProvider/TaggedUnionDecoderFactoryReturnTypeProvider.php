<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\MethodReturnTypeProvider;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\TaggedUnionDecoderFactory;
use Klimick\PsalmTest\Integration\Psalm;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type\Union;
use Klimick\PsalmDecode\Issue;
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
            $args = yield Psalm::getCallArgs($event);

            yield proveTrue($args->count() >= 2)
                ->orElse(Issue\TaggedUnion\TooFewArgsForTaggedUnionIssue::raise($event));

            yield proveTrue($args->every(fn($arg) => null !== $arg->node->name))
                ->orElse(Issue\TaggedUnion\NotNamedArgForTaggedUnionIssue::raise($event));
        });

        return null;
    }
}
