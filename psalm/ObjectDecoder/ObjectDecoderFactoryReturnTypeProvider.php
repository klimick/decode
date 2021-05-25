<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder;

use Psalm\Type;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Klimick\Decode\Decoder\ObjectDecoderFactory;

final class ObjectDecoderFactoryReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [ObjectDecoderFactory::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Type\Union
    {
        ObjectVerifier::verify($event);

        return null;
    }
}
