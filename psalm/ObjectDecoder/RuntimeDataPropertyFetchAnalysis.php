<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\RuntimeData;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Psalm\Internal\Analyzer\IssueData;
use Psalm\Internal\Analyzer\StatementsAnalyzer;
use Psalm\IssueBuffer;
use Psalm\NodeTypeProvider;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use Psalm\Type;
use SimpleXMLElement;
use function Fp\Cast\asList;
use function Fp\Collection\filter;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class RuntimeDataPropertyFetchAnalysis implements AfterExpressionAnalysisInterface, PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $registration->registerHooksFromClass(self::class);
    }

    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        $analysis = Option::do(function() use ($event) {
            $property_fetch = yield proveOf($event->getExpr(), PropertyFetch::class);

            $source = yield proveOf($event->getStatementsSource(), StatementsAnalyzer::class);
            $provider = $source->getNodeTypeProvider();

            $properties = yield self::getPropertiesFromDecoder($property_fetch, $provider);
            $identifier = yield proveOf($property_fetch->name, Identifier::class);

            yield proveTrue(array_key_exists($identifier->name, $properties));
            $provider->setType($property_fetch, $properties[$identifier->name]);

            self::removeKnownMixedPropertyFetch($source, $property_fetch);
        });

        return $analysis->get();
    }

    /**
     * @return Option<array<array-key, Type\Union>>
     */
    private static function getPropertiesFromDecoder(PropertyFetch $fetch, NodeTypeProvider $provider): Option
    {
        return Option::do(function() use ($provider, $fetch) {
            $class_string = yield Option
                ::of($provider->getType($fetch->var))
                ->map(fn(Type\Union $type) => $type->getId());

            yield proveTrue(is_a($class_string, RuntimeData::class, true));

            $decoder = yield Option::try(fn() => $class_string::definition());

            $shape_type = yield proveOf($decoder, ObjectDecoder::class)
                ->map(fn($object_decoder) => $object_decoder->shape->name())
                ->flatMap(fn($type) => Option::try(fn() => Type::parseString($type)));

            $atomics = asList($shape_type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            return yield firstOf($atomics, Type\Atomic\TKeyedArray::class)
                ->map(fn($keyed_array) => $keyed_array->properties);
        });
    }

    private static function removeKnownMixedPropertyFetch(StatementsAnalyzer $source, PropertyFetch $fetch): void
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
