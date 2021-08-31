<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Klimick\PsalmDecode\HighOrder\ConstrainedContravariantCheckHandler;
use Klimick\PsalmDecode\HighOrder\FromArgumentAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\ADT\SumTypeNewAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\ObjectDecoderFactoryReturnTypeProvider;
use Klimick\PsalmDecode\ObjectDecoder\ADT\AfterMethodAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\ADT\ProductTypeNewAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\ADT\ProductTypePropertyFetchAnalysis;
use Klimick\PsalmDecode\ObjectDecoder\ADT\SumTypeMatchAnalysis;
use Klimick\PsalmDecode\ShapeDecoder\IntersectionReturnTypeProvider;
use Klimick\PsalmDecode\HighOrder\DecoderMethodsAnalysis;
use Klimick\PsalmDecode\ShapeDecoder\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\ShapeDecoder\TupleReturnTypeProvider;
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

        $register(SumTypeMatchAnalysis::class);
        $register(ProductTypePropertyFetchAnalysis::class);
        $register(ProductTypeNewAnalysis::class);
        $register(SumTypeNewAnalysis::class);

        $register(ShapeReturnTypeProvider::class);
        $register(IntersectionReturnTypeProvider::class);
        $register(TupleReturnTypeProvider::class);

        $register(DecoderMethodsAnalysis::class);
        $register(ConstrainedContravariantCheckHandler::class);
        $register(FromArgumentAnalysis::class);
    }
}
