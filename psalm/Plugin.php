<?php /** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis\InferShapeAfterClassLikeVisit;
use Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis\InferUnionAfterClassLikeVisit;
use Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis\DecoderMethodsAnalysis;
use Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis\FromArgumentAnalysis;
use Klimick\PsalmDecode\Hook\AfterStatementAnalysis\GenerateShapeMetaMixinAfterStatementAnalysis;
use Klimick\PsalmDecode\Hook\AfterStatementAnalysis\GenerateUnionMetaMixinAfterStatementAnalysis;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\IntersectionReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\TupleReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ConstrainedMethodReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ObjectDecoderFactoryReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\TaggedUnionDecoderFactoryReturnTypeProvider;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use RuntimeException;
use SimpleXMLElement;

final class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $register = function(string $hook) use ($registration): void {
            if (!class_exists($hook)) {
                throw new RuntimeException('Something went wrong');
            }

            $registration->registerHooksFromClass($hook);
        };

        $register(ObjectDecoderFactoryReturnTypeProvider::class);

        $register(ShapeReturnTypeProvider::class);
        $register(IntersectionReturnTypeProvider::class);
        $register(TupleReturnTypeProvider::class);
        $register(TaggedUnionDecoderFactoryReturnTypeProvider::class);

        $register(DecoderMethodsAnalysis::class);
        $register(ConstrainedMethodReturnTypeProvider::class);
        $register(FromArgumentAnalysis::class);
        $register(GenerateShapeMetaMixinAfterStatementAnalysis::class);
        $register(GenerateUnionMetaMixinAfterStatementAnalysis::class);
        $register(InferShapeAfterClassLikeVisit::class);
        $register(InferUnionAfterClassLikeVisit::class);
    }
}
