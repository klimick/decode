<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterStatementAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Psalm\Aliases;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Union;
use function Fp\Collection\map;
use function str_ends_with;
use function str_replace;
use const PHP_EOL;

final class MetaMixinGenerator
{
    private const TEMPLATE = <<<TEMPLATE
    <?php
    
    declare(strict_types=1);
    
    {{MIXIN_NAMESPACE}}
    
    /** @psalm-suppress MissingConstructor */
    final class {{MIXIN_NAME}}
    {
    {{PROPS_LIST}}
    }
    TEMPLATE;

    private const PROP = <<<TEMPLATE
        /** @var %s */
        public $%s;
    TEMPLATE;

    /**
     * @param array<string, Union> $types
     */
    public static function forShape(ClassLikeStorage $storage, StatementsSource $source, array $types): void
    {
        $class = $source->getClassName();
        $path = $source->getRootFilePath();

        if (null === $class || !str_ends_with($path, "{$class}.php")) {
            return;
        }

        $in_dir = str_replace("/{$class}.php", '', $path);
        $filename = PsalmApi::$classlikes->toShortName($storage) . 'MetaMixin.php';

        file_put_contents("{$in_dir}/{$filename}", self::generateShapeMixin($storage, $types));
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
                    if ($type->possibly_undefined) {
                        $type = clone $type;
                        $type->addType(new TNull());
                    }

                    return sprintf(self::PROP, PsalmApi::$types->toDocblockString($type), $name);
                },
            )),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), self::TEMPLATE);
    }
}
