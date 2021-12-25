<?php /** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ConstrainedMethodReturnTypeProvider;
use Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis\FromArgumentAnalysis;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ObjectDecoderFactoryReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\IntersectionReturnTypeProvider;
use Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis\DecoderMethodsAnalysis;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\TupleReturnTypeProvider;
use Klimick\PsalmDecode\Hook\AfterClassLikeVisit\ProductTypeAfterClassLikeVisit;
use Klimick\PsalmDecode\Hook\AfterClassLikeVisit\SumTypeAfterClassLikeVisit;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

final class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $register =
            /** @param class-string $hook */
            function(string $hook) use ($registration): void {
                class_exists($hook);
                $registration->registerHooksFromClass($hook);
            };

        $register(ObjectDecoderFactoryReturnTypeProvider::class);

        $register(ShapeReturnTypeProvider::class);
        $register(IntersectionReturnTypeProvider::class);
        $register(TupleReturnTypeProvider::class);

        $register(DecoderMethodsAnalysis::class);
        $register(ConstrainedMethodReturnTypeProvider::class);
        $register(FromArgumentAnalysis::class);

        $register(SumTypeAfterClassLikeVisit::class);
        $register(ProductTypeAfterClassLikeVisit::class);
    }
}
