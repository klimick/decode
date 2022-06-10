<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Common;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\PsalmDecode\Plugin;
use Psalm\Aliases;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function array_keys;
use function array_values;
use function explode;
use function file_put_contents;
use function Fp\Collection\map;
use function implode;
use function is_dir;
use function mkdir;
use function sprintf;
use function str_contains;
use function str_replace;
use const PHP_EOL;

final class MetaMixinGenerator
{
    private const SHAPE_TEMPLATE = <<<PHP
    <?php
    
    declare(strict_types=1);
    
    {{MIXIN_NAMESPACE}}
    
    final class {{MIXIN_NAME}}
    {
    {{PROPS_LIST}}
    }
    PHP;

    private const UNION_TEMPLATE = <<<PHP
    <?php
    
    declare(strict_types=1);
    
    {{MIXIN_NAMESPACE}}
    
    use Closure;
    
    final class {{MIXIN_NAME}}
    {
        /** @var {{UNION_TYPE}} */
        public \$value;
    
        /**
         * @template T
         *
    {{MATCHER_LIST}}
         * @return T
         */
        public function match({{MATCHER_NATIVE_LIST}}): mixed
        {
        }
    
        /**
         * @template T of object
         *
         * @param class-string<T> \$class
         * @psalm-assert-if-true T \$this->value
         */
        public function is(string \$class): bool
        {
        }
    }
    PHP;

    private const PROP = <<<TEMPLATE
        /** @var %s */
        public $%s;
    TEMPLATE;

    private const CLOSURE = <<<TEMPLATE
         * @param Closure(%s): T \$on%s
    TEMPLATE;

    /**
     * @param array<string, Union> $props
     */
    public static function createShapeMetaMixin(ClassLikeStorage $storage, array $props): void
    {
        self::save($storage, self::shapeMixinTemplate($storage, $props));
    }

    /**
     * @param array<string, Union> $return
     */
    public static function createUnionMetaMixin(ClassLikeStorage $storage, Union $cases): void
    {
        self::save($storage, self::generateUnionMixin($storage, $cases));
    }

    private static function save(ClassLikeStorage $storage, string $template): void
    {
        $path = self::mkdir($storage);
        $filename = PsalmApi::$classlikes->toShortName($storage) . 'MetaMixin.php';

        file_put_contents("{$path}/{$filename}", $template);
    }

    private static function mkdir(ClassLikeStorage $storage): string
    {
        $namespace = str_contains($storage->name, '\\')
            ? str_replace('\\' . PsalmApi::$classlikes->toShortName($storage), '', $storage->name)
            : $storage->name;

        $dir = implode('/', [
            Plugin::getFolderForMixins(),
            ...explode('\\', $namespace),
        ]);

        if (!is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        return $dir;
    }

    /**
     * @param array<string, Union> $return
     */
    private static function shapeMixinTemplate(ClassLikeStorage $storage, array $return): string
    {
        $replacements = [
            '{{MIXIN_NAMESPACE}}' => Option::fromNullable($storage->aliases)
                ->flatMap(fn(Aliases $aliases) => Option::fromNullable($aliases->namespace))
                ->map(fn(string $class_namespace) => "namespace {$class_namespace};")
                ->getOrElse(''),
            '{{MIXIN_NAME}}' => PsalmApi::$classlikes->toShortName($storage) . 'MetaMixin',
            '{{PROPS_LIST}}' => implode(PHP_EOL, map(
                $return,
                function(Union $type, string $name) {
                    $docblockString = PsalmApi::$types->toDocblockString(
                        $type->possibly_undefined
                            ? PsalmApi::$types->asNullable($type)
                            : $type,
                    );

                    return sprintf(self::PROP, $docblockString, $name);
                },
            )),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), self::SHAPE_TEMPLATE);
    }

    private static function generateUnionMixin( ClassLikeStorage $storage, Union $union): string
    {
        $replacements = [
            '{{MIXIN_NAMESPACE}}' => Option::fromNullable($storage->aliases)
                ->flatMap(fn(Aliases $aliases) => Option::fromNullable($aliases->namespace))
                ->map(fn(string $class_namespace) => "namespace {$class_namespace};")
                ->getOrElse(''),
            '{{MIXIN_NAME}}' => PsalmApi::$classlikes->toShortName($storage) . 'MetaMixin',
            '{{UNION_TYPE}}' => PsalmApi::$types->toDocblockString($union),
            '{{MATCHER_LIST}}' => implode(PHP_EOL, map(
                $union->getAtomicTypes(),
                function(Atomic $atomic) {
                    $param_name = $atomic instanceof TNamedObject
                        ? PsalmApi::$classlikes->toShortName($atomic)
                        : $atomic->getId();

                    return sprintf(self::CLOSURE, PsalmApi::$types->toDocblockString($atomic), $param_name);
                }
            )),
            '{{MATCHER_NATIVE_LIST}}' => implode(', ', map(
                $union->getAtomicTypes(),
                function(Atomic $atomic) {
                    $param_name = $atomic instanceof TNamedObject
                        ? PsalmApi::$classlikes->toShortName($atomic)
                        : $atomic->getId();

                    return sprintf('Closure $on%s', $param_name);
                },
            )),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), self::UNION_TEMPLATE);
    }
}
