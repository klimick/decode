<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Fp\Functional\Option\Option;

use PhpParser\Node;
use Psalm\Type;
use Psalm\IssueBuffer;
use Psalm\Internal\Analyzer\IssueData;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use function Fp\Collection\filter;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class PropertyFetchAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        $analysis = Option::do(function() use ($event) {
            $property_fetch = yield proveOf($event->getExpr(), Node\Expr\PropertyFetch::class);

            $source = yield proveOf($event->getStatementsSource(), StatementsAnalyzer::class);
            $provider = $source->getNodeTypeProvider();

            $class_string = yield Option
                ::of($provider->getType($property_fetch->var))
                ->map(fn(Type\Union $type) => $type->getId());

            $properties = yield RuntimeDecoder::getProperties($class_string);
            $identifier = yield proveOf($property_fetch->name, Node\Identifier::class);

            yield proveTrue(array_key_exists($identifier->name, $properties));
            $provider->setType($property_fetch, $properties[$identifier->name]);

            self::removeKnownMixedPropertyFetch($source, $property_fetch);
        });

        return $analysis->get();
    }

    private static function removeKnownMixedPropertyFetch(StatementsAnalyzer $source, Node\Expr\PropertyFetch $fetch): void
    {
        $mixed_property_fetches = filter(
            IssueBuffer::getIssuesDataForFile($source->getFilePath()),
            fn(IssueData $i) => 'MixedPropertyFetch' === $i->type && $i->from === $fetch->getStartFilePos(),
        );

        foreach ($mixed_property_fetches as $issue) {
            IssueBuffer::remove($source->getFilePath(), 'MixedPropertyFetch', $issue->from);
        }
    }
}
