<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder;

use Psalm\Type;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Klimick\Decode\ObjectDecoderFactory;
use SimpleXMLElement;

final class ObjectDecoderFactoryReturnTypeProvider implements MethodReturnTypeProviderInterface, PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $registration->registerHooksFromClass(self::class);
    }

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
