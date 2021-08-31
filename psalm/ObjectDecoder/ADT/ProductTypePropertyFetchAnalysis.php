<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\ADT;

use Fp\Functional\Option\Option;

use Klimick\PsalmDecode\Issue\RuntimeData\UndefinedPropertyFetchIssue;
use PhpParser\Node;
use Psalm\CodeLocation;
use Psalm\Type;
use Psalm\IssueBuffer;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use function Fp\Evidence\proveOf;

final class ProductTypePropertyFetchAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        $analysis = Option::do(function() use ($event) {
            $property_fetch = yield proveOf($event->getExpr(), Node\Expr\PropertyFetch::class);

            $source = yield proveOf($event->getStatementsSource(), StatementsAnalyzer::class);
            $provider = $source->getNodeTypeProvider();

            $class_string = yield Option
                ::fromNullable($provider->getType($property_fetch->var))
                ->map(fn(Type\Union $type) => $type->getId());

            $properties = yield RuntimeDecoder::getProperties($class_string);
            $identifier = yield proveOf($property_fetch->name, Node\Identifier::class);

            if (array_key_exists($identifier->name, $properties)) {
                $provider->setType($property_fetch, $properties[$identifier->name]);
            } else {
                $code_location = new CodeLocation($source, $property_fetch);
                $issue = new UndefinedPropertyFetchIssue($code_location, $class_string, $identifier->name);

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        });

        return $analysis->get();
    }
}
