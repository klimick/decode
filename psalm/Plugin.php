<?php /** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis\InferShapeAfterClassLikeVisit;
use Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis\InferUnionAfterClassLikeVisit;
use Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis\DecoderFromAfterMethodCallAnalysis;
use Klimick\PsalmDecode\Hook\AfterStatementAnalysis\GenerateShapeMetaMixinAfterStatementAnalysis;
use Klimick\PsalmDecode\Hook\AfterStatementAnalysis\GenerateUnionMetaMixinAfterStatementAnalysis;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\IntersectionReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ConstrainedMethodReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ObjectDecoderFactoryReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ShapePickOmitMethodReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\TaggedUnionDecoderFactoryReturnTypeProvider;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use RuntimeException;
use SimpleXMLElement;
use function class_exists;
use function Fp\Collection\forAll;

final class Plugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $hooks = [
            ObjectDecoderFactoryReturnTypeProvider::class,
            ShapeReturnTypeProvider::class,
            ShapePickOmitMethodReturnTypeProvider::class,
            IntersectionReturnTypeProvider::class,
            TaggedUnionDecoderFactoryReturnTypeProvider::class,
            ConstrainedMethodReturnTypeProvider::class,
            DecoderFromAfterMethodCallAnalysis::class,
            GenerateShapeMetaMixinAfterStatementAnalysis::class,
            GenerateUnionMetaMixinAfterStatementAnalysis::class,
            InferShapeAfterClassLikeVisit::class,
            InferUnionAfterClassLikeVisit::class,
        ];

        forAll($hooks, function(string $hook) use ($registration): void {
            class_exists($hook)
                ? $registration->registerHooksFromClass($hook)
                : throw new RuntimeException('Something went wrong');
        });
    }
}
