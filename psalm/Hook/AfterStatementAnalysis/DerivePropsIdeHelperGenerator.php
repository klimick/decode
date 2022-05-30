<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterStatementAnalysis;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\Derive\Props;
use Klimick\PsalmDecode\Helper\ShapePropertiesExtractor;
use Klimick\PsalmDecode\Plugin;
use Klimick\PsalmDecode\PsalmInternal;
use PhpParser\Node\Stmt\Return_;
use Psalm\Plugin\EventHandler\AfterStatementAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterStatementAnalysisEvent;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;

/**
 * @psalm-import-type MixinConfig from Plugin
 */
final class DerivePropsIdeHelperGenerator implements AfterStatementAnalysisInterface
{
    public static function afterStatementAnalysis(AfterStatementAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $context = $event->getContext();
            $source = $event->getStatementsSource();
            $types = $source->getNodeTypeProvider();

            $config = yield proveString($context->calling_method_id)
                ->filter(fn($method) => str_ends_with($method, '::props'))
                ->flatMap(fn() => Plugin::getMixinConfig());

            $storage = yield proveString($context->self)
                ->flatMap(fn($class) => PsalmInternal::getStorageFor($class));

            $property_types = yield proveTrue(array_key_exists(strtolower(Props::class), $storage->class_implements))
                ->flatMap(fn() => proveOf($event->getStmt(), Return_::class))
                ->flatMap(fn($return) => Option::fromNullable($types->getType($return)))
                ->flatMap(fn($type) => ShapePropertiesExtractor::fromDecoder($type));

            GeneratePropsIdeHelper::for($storage, $config, $property_types);
        });

        return null;
    }
}
