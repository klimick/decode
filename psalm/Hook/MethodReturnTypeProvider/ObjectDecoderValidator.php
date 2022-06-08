<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\MethodReturnTypeProvider;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\PsalmDecode\Common\DecoderType;
use Klimick\PsalmDecode\Common\NamedArgumentsMapper;
use Klimick\PsalmDecode\Issue;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\PropertyStorage;
use Psalm\Type;
use Psalm\Type\Union;
use Psalm\Type\Atomic\TNamedObject;
use function array_key_exists;
use function array_keys;
use function Fp\Collection\first;
use function Fp\Collection\map;
use function Fp\Evidence\proveNonEmptyList;

final class ObjectDecoderValidator
{
    public static function verify(MethodReturnTypeProviderEvent $event): void
    {
        Option::do(function() use ($event) {
            self::checkPropertyTypes(
                event: $event,
                actual_shape: yield proveNonEmptyList($event->getCallArgs())
                    ->map(fn($args) => NamedArgumentsMapper::map($event->getSource(), $args))
                    ->flatMap(fn(Union $shape) => DecoderType::getShapeProperties($shape)),
                expected_shape: yield Option::fromNullable($event->getTemplateTypeParameters())
                    ->flatMap(fn(array $templates) => first($templates))
                    ->flatMap(fn(Union $template) => PsalmApi::$types->asSingleAtomicOf(TNamedObject::class, $template))
                    ->flatMap(fn(TNamedObject $object) => PsalmApi::$classlikes->getStorage($object))
                    ->map(fn(ClassLikeStorage $storage) => self::toExpectedDecoderProps($storage)),
                arg_locations: yield self::extractArgLocations($event, $event->getSource()),
            );
        });
    }

    /**
     * @return array<string, Type\Union>
     */
    private static function toExpectedDecoderProps(ClassLikeStorage $storage): array
    {
        return map($storage->properties, fn(PropertyStorage $property) => null !== $property->type
            ? PsalmApi::$types->expandUnion($storage->name, $property->type)
            : Type::getMixed());
    }

    /**
     * @return Option<array<string, CodeLocation>>
     */
    private static function extractArgLocations(MethodReturnTypeProviderEvent $event, StatementsSource $source): Option
    {
        return Option::do(function() use ($event, $source) {
            $arg_locations = [];

            foreach ($event->getCallArgs() as $arg) {
                $identifier = yield Option::fromNullable($arg->name);
                $arg_locations[$identifier->name] = new CodeLocation($source, $arg);
            }

            return $arg_locations;
        });
    }

    /**
     * @param array<string, Type\Union> $actual_shape
     * @param array<string, Type\Union> $expected_shape
     * @param array<string, CodeLocation> $arg_locations
     */
    private static function checkPropertyTypes(
        MethodReturnTypeProviderEvent $event,
        array $actual_shape,
        array $expected_shape,
        array $arg_locations,
    ): void
    {
        $source = $event->getSource();
        $method_code_location = $event->getCodeLocation();

        $missing_properties = [];

        foreach ($expected_shape as $property => $type) {
            if (!array_key_exists($property, $actual_shape)) {
                $missing_properties[] = $property;
            } elseif (!PsalmApi::$types->isTypeContainedByType($actual_shape[$property], $type)) {
                $issue = new Issue\InvalidDecoderForProperty(
                    property: $property,
                    actual_type: $actual_shape[$property],
                    expected_type: $type,
                    code_location: $arg_locations[$property] ?? $method_code_location,
                );

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        }

        if (!empty($missing_properties)) {
            $issue = new Issue\RequiredObjectPropertyMissing(
                missing_properties: $missing_properties,
                code_location: $method_code_location,
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }

        foreach (array_keys($actual_shape) as $property) {
            if (!array_key_exists($property, $expected_shape)) {
                $issue = new Issue\NonexistentPropertyObjectProperty(
                    property: $property,
                    code_location: $arg_locations[$property] ?? $method_code_location,
                );

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        }
    }
}
