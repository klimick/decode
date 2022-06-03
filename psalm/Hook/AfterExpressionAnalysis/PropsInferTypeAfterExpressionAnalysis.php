<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterExpressionAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\Derive;
use Klimick\PsalmDecode\Helper\DecoderType;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Storage\ClassLikeStorage;
use function Fp\Collection\reindex;
use function Fp\Evidence\proveNonEmptyArray;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;

final class PropsInferTypeAfterExpressionAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $method_call = yield proveOf($event->getExpr(), StaticCall::class)
                ->filter(fn(StaticCall $call) => proveOf($call->name, Identifier::class)
                    ->map(fn(Identifier $id) => $id->name)
                    ->map(fn(string $name) => 'props' === $name)
                    ->getOrElse(false));

            $properties = yield proveOf($method_call->class, Name::class)
                ->flatMap(fn(Name $id) => proveString($id->getAttribute('resolvedName')))
                ->filter(fn(string $class) => PsalmApi::$classlikes->classImplements($class, Derive\Props::class))
                ->flatMap(fn(string $class) => PsalmApi::$classlikes->getStorage($class))
                ->flatMap(fn(ClassLikeStorage $storage) => proveNonEmptyArray($storage->pseudo_property_get_types));

            $dollar_len = strlen('$');
            $shape_props = reindex($properties, fn($_, $prop) => substr($prop, $dollar_len));

            $source = $event->getStatementsSource();
            $types = $source->getNodeTypeProvider();

            $type = yield DecoderType::withShapeDecoderIntersection(
                DecoderType::createShapeDecoder($shape_props),
            );
            $types->setType($method_call, $type);
        });

        return null;
    }
}
