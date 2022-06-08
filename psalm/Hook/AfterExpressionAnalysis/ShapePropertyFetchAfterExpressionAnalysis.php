<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterExpressionAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\InferShape;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Psalm\CodeLocation;
use Psalm\Issue\UndefinedMagicPropertyFetch;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Type\Atomic\TNamedObject;
use Psalm\Type\Union;
use function array_key_exists;
use function Fp\Evidence\proveOf;

final class ShapePropertyFetchAfterExpressionAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $property_fetch = yield proveOf($event->getExpr(), PropertyFetch::class);

            $name = yield proveOf($property_fetch->name, Identifier::class)
                ->map(fn(Identifier $id) => $id->name);

            $storage = yield PsalmApi::$types->getType($event, $property_fetch->var)
                ->flatMap(fn(Union $type) => PsalmApi::$types->asSingleAtomicOf(TNamedObject::class, $type))
                ->filter(fn(TNamedObject $object) => PsalmApi::$classlikes->classImplements($object, InferShape::class))
                ->flatMap(fn(TNamedObject $object) => PsalmApi::$classlikes->getStorage($object));

            // complete class storage here

            if (!array_key_exists('$' . $name, $storage->pseudo_property_get_types) && !array_key_exists($name, $storage->declaring_property_ids)) {
                $property_id = "{$storage->name}::{$name}";
                $source = $event->getStatementsSource();

                $issue = new UndefinedMagicPropertyFetch(
                    message: 'Magic instance property ' . $property_id . ' is not defined',
                    code_location: new CodeLocation($source, $property_fetch),
                    property_id: $property_id
                );

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        });

        return null;
    }
}
