<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Klimick\PsalmDecode\Constraint\ConstrainedContravariantCheckHandler;
use Klimick\PsalmDecode\ObjectDecoder\ObjectDecoderFactoryReturnTypeProvider;
use Klimick\PsalmDecode\ObjectDecoder\RuntimeData\AfterMethodAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\RuntimeData\DefinitionCallAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\RuntimeData\DefinitionReturnAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\RuntimeData\OfCallAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\RuntimeData\PropertyFetchAnalysis;
use Klimick\PsalmDecode\ShapeDecoder\IntersectionReturnTypeProvider;
use Klimick\PsalmDecode\ShapeDecoder\DecoderMethodsAnalysis;
use Klimick\PsalmDecode\ShapeDecoder\ShapeReturnTypeProvider;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

final class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $register = function(string $hook) use ($registration): void {
            class_exists($hook);
            $registration->registerHooksFromClass($hook);
        };

        $register(ObjectDecoderFactoryReturnTypeProvider::class);

        $register(DefinitionCallAnalysis::class);
        $register(DefinitionReturnAnalysis::class);
        $register(PropertyFetchAnalysis::class);
        $register(OfCallAnalysis::class);

        $register(IntersectionReturnTypeProvider::class);
        $register(DecoderMethodsAnalysis::class);
        $register(ShapeReturnTypeProvider::class);

        $register(ConstrainedContravariantCheckHandler::class);
    }
}
