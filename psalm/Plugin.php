<?php /** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis\DerivePropsVisitor;
use Klimick\PsalmDecode\Hook\AfterExpressionAnalysis\PossiblyUndefinedToNullableAfterExpressionAnalysis;
use Klimick\PsalmDecode\Hook\AfterExpressionAnalysis\PropsInferTypeAfterExpressionAnalysis;
use Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis\DecoderMethodsAnalysis;
use Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis\FromArgumentAnalysis;
use Klimick\PsalmDecode\Hook\AfterStatementAnalysis\DerivePropsIdeHelperGenerator;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\IntersectionReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\TupleReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ConstrainedMethodReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ObjectDecoderFactoryReturnTypeProvider;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\TaggedUnionDecoderFactoryReturnTypeProvider;
use Psalm\Config;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use RuntimeException;
use SimpleXMLElement;

/**
 * @psalm-type MixinConfig = array{
 *     directory: non-empty-string,
 *     namespace: non-empty-string
 * }
 */
final class Plugin implements PluginEntryPointInterface
{
    /** @var MixinConfig|null */
    private static array|null $mixinConfig = null;

    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $register = function(string $hook) use ($registration): void {
            if (!class_exists($hook)) {
                throw new RuntimeException('Something went wrong');
            }

            $registration->registerHooksFromClass($hook);
        };

        self::$mixinConfig = self::resolveMixinConfig($config);

        $register(ObjectDecoderFactoryReturnTypeProvider::class);

        $register(ShapeReturnTypeProvider::class);
        $register(IntersectionReturnTypeProvider::class);
        $register(TupleReturnTypeProvider::class);
        $register(TaggedUnionDecoderFactoryReturnTypeProvider::class);

        $register(DecoderMethodsAnalysis::class);
        $register(ConstrainedMethodReturnTypeProvider::class);
        $register(FromArgumentAnalysis::class);
        $register(DerivePropsIdeHelperGenerator::class);
        $register(DerivePropsVisitor::class);
        $register(PropsInferTypeAfterExpressionAnalysis::class);
        $register(PossiblyUndefinedToNullableAfterExpressionAnalysis::class);
    }

    /**
     * @return Option<MixinConfig>
     */
    public static function getMixinConfig(): Option
    {
        return Option::fromNullable(self::$mixinConfig);
    }

    /**
     * @return MixinConfig|null
     */
    private static function resolveMixinConfig(?SimpleXMLElement $config = null): ?array
    {
        if (isset($config->{'derived-props-mixin-path'}) && isset($config->{'derived-props-mixin-namespace'})) {
            $fullPath = Config::getInstance()->base_dir . trim((string) $config->{'derived-props-mixin-path'});

            if (empty($fullPath) || !is_dir($fullPath)) {
                throw new RuntimeException("{$fullPath} is not a directory");
            }

            $namespace = trim((string) $config->{'derived-props-mixin-namespace'});

            if (empty($namespace)) {
                throw new RuntimeException('Namespace cannot be empty');
            }

            return [
                'directory' => $fullPath,
                'namespace' => $namespace,
            ];
        }

        return null;
    }
}
