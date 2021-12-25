<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Fp\Functional\Option\Option;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\PsalmDecode\Issue\Object\NotPartialPropertyIssue;
use Klimick\PsalmTest\Integration\Psalm;
use Psalm\Codebase;
use Psalm\CodeLocation;
use Psalm\Internal\Type\TypeExpander;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type;
use function Fp\Cast\asList;
use function Fp\Collection\first;
use function Fp\Collection\second;

final class ObjectVerifier
{
    public static function verify(MethodReturnTypeProviderEvent $event): void
    {
        Option::do(function() use ($event) {
            $source = $event->getSource();
            $codebase = $source->getCodebase();

            $actual_shape = yield NamedArgumentsMapper::map($event->getCallArgs(), $source->getNodeTypeProvider())
                ->map(
                    fn($properties) => new Type\Union([
                        new Type\Atomic\TGenericObject(ShapeDecoder::class, [
                            new Type\Union([$properties])
                        ]),
                    ])
                )
                ->flatMap(fn($shape_decoder) => ShapePropertiesExtractor::fromDecoder($shape_decoder));

            $call_info = yield self::extractCallInfo($event);

            self::compareSideBySide(
                codebase: $codebase,
                source: $source,
                actual_shape: $actual_shape,
                expected_shape: $call_info['expected_shape'],
                method_code_location: $call_info['call_location'],
                arg_code_locations: $call_info['arg_locations'],
            );
        });
    }

    /**
     * @psalm-type CallInfo = array{
     *     expected_shape: array<string, Type\Union>,
     *     arg_locations: array<string, CodeLocation>,
     *     call_location: CodeLocation
     * }
     *
     * @return Option<CallInfo>
     */
    private static function extractCallInfo(MethodReturnTypeProviderEvent $event): Option
    {
        return Option::do(function() use ($event) {
            $source = $event->getSource();
            $codebase = $source->getCodebase();

            $params = asList($event->getTemplateTypeParameters() ?? []);

            $object_class_type_param = yield first($params);
            $partial_type_param = yield second($params);

            $is_partial = yield self::extractPartialityInfo($partial_type_param);
            $class_storage = yield self::extractClassStorage($object_class_type_param, $codebase);
            $arg_locations = yield self::extractArgLocations($event, $source);

            $call_location = $event->getCodeLocation();
            $decoder_type = self::inferDecoderType($codebase, $source, $class_storage, $call_location, $arg_locations, $is_partial);
            $expected_shape = yield ShapePropertiesExtractor::fromDecoder($decoder_type);

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
        Codebase $codebase,
        StatementsSource $source,
        ClassLikeStorage $class_storage,
        CodeLocation $call_location,
        array $arg_locations,
        bool $partial,
    ): Type\Union
    {
        $shape = [];

        foreach ($class_storage->properties as $property => $storage) {
            $shape[$property] = self::expandType($codebase, $class_storage, $storage->type ?? Type::getMixed());

            if ($partial && !$shape[$property]->isNullable()) {
                $issue = new NotPartialPropertyIssue(
                    property: $property,
                    code_location: $arg_locations[$property] ?? $call_location,
                );

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        }

        return new Type\Union([
            new Type\Atomic\TGenericObject(ShapeDecoder::class, [
                new Type\Union([
                    DecoderType::createShape($shape)
                ]),
            ])
        ]);
    }

    private static function expandType(
        Codebase $codebase,
        ClassLikeStorage $class,
        Type\Union $propertyType,
    ): Type\Union
    {
        return TypeExpander::expandUnion(
            codebase: $codebase,
            return_type: $propertyType,
            self_class: $class->name,
            static_class_type: null,
            parent_class: null,
        );
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
        return Psalm::asSingleAtomicOf(Type\Atomic\TBool::class, $partial_type_param)
            ->map(fn($bool) => match (true) {
                ($bool instanceof Type\Atomic\TTrue) => true,
                ($bool instanceof Type\Atomic\TFalse) => false,
            });
    }

    /**
     * @return Option<ClassLikeStorage>
     */
    private static function extractClassStorage(Type\Union $object_class, Codebase $codebase): Option
    {
        return Psalm::asSingleAtomicOf(Type\Atomic\TNamedObject::class, $object_class)->flatMap(
            fn($named_object) => Option::try(fn() => $codebase->classlike_storage_provider->get($named_object->value))
        );
    }

    /**
     * @param array<string, Type\Union> $actual_shape
     * @param array<string, Type\Union> $expected_shape
     * @param array<string, CodeLocation> $arg_code_locations
     */
    private static function compareSideBySide(
        Codebase $codebase,
        StatementsSource $source,
        array $actual_shape,
        array $expected_shape,
        CodeLocation $method_code_location,
        array $arg_code_locations,
    ): void
    {
        ObjectPropertiesValidator::checkPropertyTypes($codebase, $source, $method_code_location, $arg_code_locations, $expected_shape, $actual_shape);
        ObjectPropertiesValidator::checkNonexistentProperties($actual_shape, $expected_shape, $arg_code_locations, $source, $method_code_location);
        ObjectPropertiesValidator::checkMissingProperties($source, $method_code_location, $expected_shape, $actual_shape);
    }
}
