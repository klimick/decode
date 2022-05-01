<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Klimick\PsalmDecode\Plugin;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Union;
use function Fp\Collection\keys;
use function Fp\Collection\map;
use const PHP_EOL;

/**
 * @psalm-import-type MixinConfig from Plugin
 */
final class DerivedPropsClassHandler
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
    public static function handle(ClassLikeStorage $storage, array $config, array $types): void
    {
        $filename = self::getMixinClassFilename($storage);
        $path = self::createFolder($storage, $config['directory']);

        file_put_contents("{$path}/{$filename}", self::generateTemplate($storage, $types, $config['namespace']));
    }

    private static function createFolder(ClassLikeStorage $storage, string $inDir): string
    {
        $directories = explode('\\', str_replace(strrchr($storage->name, '\\'), '', $storage->name));

        foreach ($directories as $directory) {
            $inDir = "{$inDir}/{$directory}";

            if (!is_dir($inDir)) {
                mkdir($inDir);
            }
        }

        return $inDir;
    }

    private static function getMixinClassFilename(ClassLikeStorage $storage): string
    {
        return substr(strrchr($storage->name, '\\'), 1) . 'Props.php';
    }

    /**
     * @param array<string, Union> $return
     */
    private static function generateTemplate(ClassLikeStorage $storage, array $return, string $namespace): string
    {
        $class_namespace = str_replace(strrchr($storage->name, '\\'), '', $storage->name);

        $replacements = [
            '{{MIXIN_NAMESPACE}}' => !empty($class_namespace)
                ? "{$namespace}\\$class_namespace"
                : $namespace,
            '{{MIXIN_NAME}}' => substr(strrchr($storage->name, '\\'), 1) . 'Props',
            '{{PROPS_LIST}}' => implode(PHP_EOL, map(
                $return,
                fn($type, $name) => sprintf(self::PROP, UnionToString::for($type), $name),
            )),
            '{{CREATE_DOCBLOCK}}' => sprintf(self::CREATE_DOCBLOCK,
                implode(PHP_EOL, map(
                    $return,
                    fn($type, $name) => sprintf(self::DOCBLOCK_PARAM, UnionToString::for($type), $name),
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
