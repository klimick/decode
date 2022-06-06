<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterStatementAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Psalm\Aliases;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function Fp\Collection\map;
use function implode;
use function sprintf;
use function str_ends_with;
use function str_replace;
use function file_put_contents;
use const PHP_EOL;

final class MetaMixinGenerator
{
    private const SHAPE_TEMPLATE = <<<PHP
    <?php
    
    declare(strict_types=1);
    
    {{MIXIN_NAMESPACE}}
    
    /** @psalm-suppress MissingConstructor */
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
    
    /** @psalm-suppress MissingConstructor */
    final class {{MIXIN_NAME}}
    {
        /** @var {{UNION_TYPE}} */
        public \$value;
    
        /**
         * @template T
         *
    {{MATCHER_LIST}}
         * @return T
         * @psalm-suppress InvalidReturnType
         */
        public function match({{MATCHER_NATIVE_LIST}}): mixed
        {
        }
    
        /**
         * @template T of object
         *
         * @param class-string<T> \$class
         * @psalm-assert-if-true T \$this->value
         * @psalm-suppress InvalidReturnType
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
     * @param array<string, Union> $types
     */
    public static function forShape(ClassLikeStorage $storage, StatementsSource $source, array $types): void
    {
        self::save($storage, $source, self::generateShapeMixin($storage, $types));
    }

    public static function forUnion(ClassLikeStorage $storage, StatementsSource $source, Union $union): void
    {
        self::save($storage, $source, self::generateUnionMixin($storage, $union));
    }

    private static function save(ClassLikeStorage $storage, StatementsSource $source, string $template): void
    {
        $class = $source->getClassName();
        $path = $source->getRootFilePath();

        if (null !== $class && str_ends_with($path, "{$class}.php")) {
            $in_dir = str_replace("/{$class}.php", '', $path);
            $filename = PsalmApi::$classlikes->toShortName($storage) . 'MetaMixin.php';

            file_put_contents("{$in_dir}/{$filename}", $template);
        }
    }

    private static function generateUnionMixin(ClassLikeStorage $storage, Union $union): string
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

    /**
     * @param array<string, Union> $return
     */
    private static function generateShapeMixin(ClassLikeStorage $storage, array $return): string
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
}
