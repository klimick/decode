<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterStatementAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\PsalmDecode\Plugin;
use Psalm\Aliases;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Union;
use function Fp\Collection\keys;
use function Fp\Collection\map;
use const PHP_EOL;

/**
 * @psalm-import-type MixinConfig from Plugin
 */
final class GeneratePropsIdeHelper
{
    private const TEMPLATE = <<<CLASS
    <?php
    
    declare(strict_types=1);
    
    namespace {{MIXIN_NAMESPACE}};
    
    final class {{MIXIN_NAME}}
    {
    {{PROPS_LIST}}
    
    {{CREATE_DOCBLOCK}}
        public static function create(
    {{PARAM_LIST}}
        ) {
            die('???');
        }
    }
    
    CLASS;

    private const PROP = <<<PROP
        /** @var %s */
        public $%s;
    PROP;

    private const DOCBLOCK_PARAM = '     * @param %s $%s';

    private const NATIVE_PARAM = '        $%s,';

    private const CREATE_DOCBLOCK = <<<DOCK
        /**
    %s
         * @return \%s
         */
    DOCK;

    /**
     * @param array<string, Union> $types
     * @psalm-param MixinConfig $config
     */
    public static function for(ClassLikeStorage $storage, array $config, array $types): void
    {
        $filename = PsalmApi::$classlikes->toShortName($storage) . 'Props.php';
        $path = self::createFolder($storage, $config['directory']);

        file_put_contents("{$path}/{$filename}", self::generateTemplate($storage, $types, $config['namespace']));
    }

    private static function createFolder(ClassLikeStorage $storage, string $inDir): string
    {
        $directories = Option::fromNullable($storage->aliases)
            ->flatMap(fn(Aliases $aliases) => Option::fromNullable($aliases->namespace))
            ->map(fn(string $namespace) => explode('\\', $namespace))
            ->getOrElse([]);

        foreach ($directories as $directory) {
            $inDir = "{$inDir}/{$directory}";

            if (!is_dir($inDir)) {
                mkdir($inDir);
            }
        }

        return $inDir;
    }

    /**
     * @param array<string, Union> $return
     */
    private static function generateTemplate(ClassLikeStorage $storage, array $return, string $namespace): string
    {
        $replacements = [
            '{{MIXIN_NAMESPACE}}' => Option::fromNullable($storage->aliases)
                ->flatMap(fn(Aliases $aliases) => Option::fromNullable($aliases->namespace))
                ->map(fn(string $class_namespace) => "{$namespace}\\$class_namespace")
                ->getOrElse($namespace),
            '{{MIXIN_NAME}}' => PsalmApi::$classlikes->toShortName($storage) . 'Props',
            '{{PROPS_LIST}}' => implode(PHP_EOL, map(
                $return,
                fn($type, $name) => sprintf(self::PROP, PsalmApi::$types->toDocblockString($type), $name),
            )),
            '{{CREATE_DOCBLOCK}}' => sprintf(self::CREATE_DOCBLOCK,
                implode(PHP_EOL, map(
                    $return,
                    fn($type, $name) => sprintf(self::DOCBLOCK_PARAM, PsalmApi::$types->toDocblockString($type), $name),
                )),
                $storage->name
            ),
            '{{PARAM_LIST}}' => implode(PHP_EOL, map(
                keys($return),
                fn($param) => sprintf(self::NATIVE_PARAM, $param),
            )),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), self::TEMPLATE);
    }
}
