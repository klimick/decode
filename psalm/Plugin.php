<?php /** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis\InferShapeAfterClassLikeVisit;
use Klimick\PsalmDecode\Hook\AfterClassLikeAnalysis\InferUnionAfterClassLikeVisit;
use Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis\DecoderFromAfterMethodCallAnalysis;
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
use function trim;

/**
 * @psalm-type Config = array{
 *     folder_for_mixins: string,
 *     generation_enable: bool
 * }
 */
final class Plugin implements PluginEntryPointInterface
{
    /** @var Config */
    private static array $mixin_config = [
        'folder_for_mixins' => 'mixin',
        'generation_enable' => false,
    ];

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
            InferShapeAfterClassLikeVisit::class,
            InferUnionAfterClassLikeVisit::class,
        ];

        forAll($hooks, function(string $hook) use ($registration): void {
            class_exists($hook)
                ? $registration->registerHooksFromClass($hook)
                : throw new RuntimeException('Something went wrong');
        });

        self::$mixin_config = self::getMixingGenerationConfig($config);
    }

    public static function isMixinGenerationEnabled(): bool
    {
        return self::$mixin_config['generation_enable'];
    }

    public static function getFolderForMixins(): string
    {
        return PsalmApi::$codebase->config->base_dir . '/' . self::$mixin_config['folder_for_mixins'];
    }

    /**
     * @return Config
     */
    private static function getMixingGenerationConfig(?SimpleXMLElement $config = null): array
    {
        $folder_from_config = isset($config->metaMixinPath) ? trim((string) $config->metaMixinPath) : '';
        $folder_for_mixins = empty($folder_from_config) ? 'mixin' : $folder_from_config;

        $active_from_config = isset($config->generateMetaMixin) ? trim((string) $config->generateMetaMixin) : '';
        $generation_enable = $active_from_config === 'true';

        return [
            'folder_for_mixins' => $folder_for_mixins,
            'generation_enable' => $generation_enable,
        ];
    }
}
