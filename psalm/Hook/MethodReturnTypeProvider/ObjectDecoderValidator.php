<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\MethodReturnTypeProvider;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\PsalmDecode\Common\DecoderType;
use Klimick\PsalmDecode\Common\NamedArgumentsMapper;
use Klimick\PsalmDecode\Issue\Object\InvalidDecoderForPropertyIssue;
use Klimick\PsalmDecode\Issue\Object\NonexistentPropertyObjectPropertyIssue;
use Klimick\PsalmDecode\Issue\Object\NotPartialPropertyIssue;
use Klimick\PsalmDecode\Issue\Object\RequiredObjectPropertyMissingIssue;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type;
use function array_key_exists;
use function array_keys;
use function Fp\Cast\asList;
use function Fp\Collection\first;
use function Fp\Collection\second;

final class ObjectDecoderValidator
{
    public static function verify(MethodReturnTypeProviderEvent $event): void
    {
        Option::do(function() use ($event) {
            $actual_shape = yield NamedArgumentsMapper::map($event->getCallArgs(), $event->getSource())
                ->flatMap(fn($shape_decoder) => DecoderType::getShapeProperties($shape_decoder));

            $call_info = yield self::extractCallInfo($event);

            self::checkPropertyTypes(
                source: $event->getSource(),
                actual_shape: $actual_shape,
                expected_shape: $call_info['expected_shape'],
                method_code_location: $call_info['call_location'],
                arg_code_locations: $call_info['arg_locations'],
            );
        });
    }

    /**
     * @return Option<array{
     *     expected_shape: array<string, Type\Union>,
     *     arg_locations: array<string, CodeLocation>,
     *     call_location: CodeLocation
     * }>
     */
    private static function extractCallInfo(MethodReturnTypeProviderEvent $event): Option
    {
        return Option::do(function() use ($event) {
            $source = $event->getSource();
            $call_location = $event->getCodeLocation();

            $params = asList($event->getTemplateTypeParameters() ?? []);

            $object_class_type_param = yield first($params);
            $partial_type_param = yield second($params);

            $is_partial = yield self::extractPartialityInfo($partial_type_param);
            $class_storage = yield self::extractClassStorage($object_class_type_param);
            $arg_locations = yield self::extractArgLocations($event, $source);

            $decoder_type = self::inferDecoderType($source, $class_storage, $call_location, $arg_locations, $is_partial);
            $expected_shape = yield DecoderType::getShapeProperties($decoder_type);

            return [
                'call_location' => $call_location,
                'arg_locations' => $arg_locations,
                'expected_shape' => $expected_shape,
            ];
        });
    }

    /**
     * @param array<string, CodeLocation> $arg_locations
     */
    private static function inferDecoderType(
        StatementsSource $source,
        ClassLikeStorage $class_storage,
        CodeLocation $call_location,
        array $arg_locations,
        bool $partial,
    ): Type\Union
    {
        $shape = [];

        foreach ($class_storage->properties as $property => $storage) {
            $shape[$property] = null !== $storage->type
                ? PsalmApi::$types->expandUnion($class_storage->name, $storage->type)
                : Type::getMixed();

            if ($partial && !$shape[$property]->isNullable()) {
                $issue = new NotPartialPropertyIssue(
                    property: $property,
                    code_location: $arg_locations[$property] ?? $call_location,
                );

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        }

        return DecoderType::createShapeDecoder($shape);
    }

    /**
     * @return Option<array<string, CodeLocation>>
     */
    private static function extractArgLocations(
        MethodReturnTypeProviderEvent $event,
        StatementsSource $source,
    ): Option
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
     * @return Option<bool>
     */
    private static function extractPartialityInfo(Type\Union $partial_type_param): Option
    {
        return PsalmApi::$types->asSingleAtomicOf(Type\Atomic\TBool::class, $partial_type_param)
            ->map(fn($bool) => match (true) {
                ($bool instanceof Type\Atomic\TTrue) => true,
                ($bool instanceof Type\Atomic\TFalse) => false,
            });
    }

    /**
     * @return Option<ClassLikeStorage>
     */
    private static function extractClassStorage(Type\Union $object_class): Option
    {
        return PsalmApi::$types->asSingleAtomicOf(Type\Atomic\TNamedObject::class, $object_class)
            ->flatMap(fn($named_object) => PsalmApi::$classlikes->getStorage($named_object));
    }

    /**
     * @param array<string, Type\Union> $actual_shape
     * @param array<string, Type\Union> $expected_shape
     * @param array<string, CodeLocation> $arg_code_locations
     */
    private static function checkPropertyTypes(
        StatementsSource $source,
        array $actual_shape,
        array $expected_shape,
        CodeLocation $method_code_location,
        array $arg_code_locations,
    ): void
    {
        $missing_properties = [];

        foreach ($expected_shape as $property => $type) {
            if (!array_key_exists($property, $actual_shape)) {
                $missing_properties[] = $property;
            } elseif (!PsalmApi::$types->isTypeContainedByType($actual_shape[$property], $type)) {
                $issue = new InvalidDecoderForPropertyIssue(
                    property: $property,
                    actual_type: $actual_shape[$property],
                    expected_type: $type,
                    code_location: $arg_code_locations[$property] ?? $method_code_location,
                );

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        }

        if (!empty($missing_properties)) {
            $issue = new RequiredObjectPropertyMissingIssue(
                missing_properties: $missing_properties,
                code_location: $method_code_location,
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }

        foreach (array_keys($actual_shape) as $property) {
            if (!array_key_exists($property, $expected_shape)) {
                $issue = new NonexistentPropertyObjectPropertyIssue(
                    property: $property,
                    code_location: $arg_code_locations[$property] ?? $method_code_location,
                );

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        }
    }
}