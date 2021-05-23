<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Psalm\Type;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Klimick\Decode\Decoder;
use Fp\Functional\Option\Option;
use function Fp\Evidence\proveTrue;

final class ConstrainedReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [Decoder::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Type\Union
    {
        Option::do(function() use ($event) {
            yield proveTrue('constrained' === $event->getMethodNameLowercase());
            // todo: implement contravariant argument check
        });

        return null;
    }
}
