<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterStatementAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\InferShape;
use Klimick\PsalmDecode\Helper\DecoderType;
use PhpParser\Node\Stmt\Return_;
use Psalm\Plugin\EventHandler\AfterStatementAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterStatementAnalysisEvent;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function is_string;

final class GenerateShapeMetaMixinAfterStatementAnalysis implements AfterStatementAnalysisInterface
{
    public static function afterStatementAnalysis(AfterStatementAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $context = yield Option::some($event->getContext())
                ->filter(fn($ctx) => is_string($ctx->calling_method_id) && str_ends_with($ctx->calling_method_id, '::shape'));

            $storage = yield proveString($context->self)
                ->filter(fn($class) => PsalmApi::$classlikes->classImplements($class, InferShape::class))
                ->flatMap(fn($class) => PsalmApi::$classlikes->getStorage($class));

            $property_types = yield proveOf($event->getStmt(), Return_::class)
                ->flatMap(fn($return) => PsalmApi::$types->getType($event, $return))
                ->flatMap(fn($type) => DecoderType::getShapeProperties($type));

            MetaMixinGenerator::forShape($storage, $event->getStatementsSource(), $property_types);
        });

        return null;
    }
}
