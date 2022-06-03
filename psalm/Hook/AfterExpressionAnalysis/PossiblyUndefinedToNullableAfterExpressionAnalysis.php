<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterExpressionAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\Derive\Props;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Atomic\TNull;
use Psalm\Type\Union;
use function Fp\Collection\at;
use function Fp\Evidence\proveOf;

final class PossiblyUndefinedToNullableAfterExpressionAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $property_fetch = yield proveOf($event->getExpr(), PropertyFetch::class);
            $property_name = yield proveOf($property_fetch->name, Identifier::class)
                ->map(fn(Identifier $id) => '$' . $id->name);

            $property_type = yield PsalmApi::$types->getType($event, $property_fetch->var)
                ->flatMap(fn(Union $type) => PsalmApi::$types->asSingleAtomicOf(TNamedObject::class, $type))
                ->filter(fn(TNamedObject $object) => PsalmApi::$classlikes->classImplements($object, Props::class))
                ->flatMap(fn(TNamedObject $object) => PsalmApi::$classlikes->getStorage($object))
                ->flatMap(fn(ClassLikeStorage $storage) => at($storage->pseudo_property_get_types, $property_name))
                ->filter(fn(Union $type) => $type->possibly_undefined);

            $nullable = clone $property_type;
            $nullable->addType(new TNull());

            $source = $event->getStatementsSource();
            $types = $source->getNodeTypeProvider();

            $types->setType($property_fetch, $nullable);
        });

        return null;
    }
}
