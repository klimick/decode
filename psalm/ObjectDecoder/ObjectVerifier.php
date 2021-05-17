<?php /** @noinspection PhpInternalEntityUsedInspection */

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder;

use Psalm\Type;
use Psalm\Codebase;
use Psalm\IssueBuffer;
use Psalm\CodeLocation;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Internal\Type\TypeExpander;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Klimick\PsalmDecode\DecodeIssue;
use Klimick\Decode\DecoderInterface;
use Klimick\PsalmDecode\NamedArguments\NamedArgumentsMapper;
use Fp\Functional\Option\Option;
use function Fp\Cast\asList;
use function Fp\Collection\first;
use function Fp\Collection\firstOf;
use function Fp\Collection\second;
use function Fp\Evidence\proveTrue;

final class ObjectVerifier
{
    public static function verify(MethodReturnTypeProviderEvent $event): void
    {
        Option::do(function() use ($event) {
            $source = $event->getSource();
            $codebase = $source->getCodebase();

            $actual_shape = yield NamedArgumentsMapper::map(
                call_args: $event->getCallArgs(),
                source: $source,
                provider: $source->getNodeTypeProvider(),
                codebase: $source->getCodebase(),
            );

            $call_info = yield self::extractCallInfo($event);

            self::compareSideBySide(
                codebase: $codebase,
                actual_decoder_type: $actual_shape,
                expected_decoder_type: $call_info['expected_shape'],
                method_code_location: $call_info['call_location'],
                arg_code_locations: $call_info['arg_locations'],
            );
        });
    }

    /**
     * @psalm-type CallInfo = array{
     *     expected_shape: Type\Union,
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
            proveTrue(2 === count($params));

            $object_class_type_param = yield first($params);
            $partial_type_param = yield second($params);

            $is_partial = yield self::extractPartialityInfo($partial_type_param);
            $class_storage = yield self::extractClassStorage($object_class_type_param, $codebase);
            $arg_locations = yield self::extractArgLocations($event, $source);

            $call_location = $event->getCodeLocation();
            $decoder_type = self::inferDecoderType($codebase, $class_storage, $call_location, $arg_locations, $is_partial);

            return [
                'call_location' => $call_location,
                'arg_locations' => $arg_locations,
                'expected_shape' => $decoder_type,
            ];
        });
    }

    /**
     * @param array<string, CodeLocation> $arg_locations
     */
    private static function inferDecoderType(
        Codebase $codebase,
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
                $issue = DecodeIssue::notPartialProperty(
                    property: $property,
                    code_location: $arg_locations[$property] ?? $call_location,
                );

                IssueBuffer::accepts($issue);
            }
        }

        $mapped_shape = new Type\Union([
            empty($shape)
                ? new Type\Atomic\TArray([Type::getEmpty(), Type::getEmpty()])
                : new Type\Atomic\TKeyedArray($shape),
        ]);

        $inferred_decoder = new Type\Atomic\TGenericObject(DecoderInterface::class, [$mapped_shape]);

        return new Type\Union([$inferred_decoder]);
    }

    /**
     * @psalm-suppress InternalClass, InternalMethod
     */
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
                $identifier = yield Option::of($arg->name);
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
        return Option::do(function() use ($partial_type_param) {
            $atomics = asList($partial_type_param->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            return yield firstOf($atomics, Type\Atomic\TBool::class)
                ->map(fn($bool) => match (true) {
                    ($bool instanceof Type\Atomic\TTrue) => true,
                    ($bool instanceof Type\Atomic\TFalse) => false,
                });
        });
    }

    /**
     * @return Option<ClassLikeStorage>
     * @psalm-suppress InternalMethod
     */
    private static function extractClassStorage(Type\Union $object_class, Codebase $codebase): Option
    {
        return Option::do(function() use ($object_class, $codebase) {
            $atomics = asList($object_class->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $named_object = yield firstOf($atomics, Type\Atomic\TNamedObject::class);

            return yield Option::try(
                fn() => $codebase->classlike_storage_provider->get($named_object->value)
            );
        });
    }

    /**
     * @param array<string, CodeLocation> $arg_code_locations
     */
    private static function compareSideBySide(
        Codebase $codebase,
        Type\Union $actual_decoder_type,
        Type\Union $expected_decoder_type,
        CodeLocation $method_code_location,
        array $arg_code_locations,
    ): void
    {
        Option::do(function() use ($actual_decoder_type, $expected_decoder_type, $method_code_location, $arg_code_locations, $codebase) {
            $actual_shape = yield self::extractShapeFromDecoderTypeParam($actual_decoder_type);
            $expected_shape = yield self::extractShapeFromDecoderTypeParam($expected_decoder_type);

            ObjectPropertiesValidator::checkPropertyTypes($codebase, $method_code_location, $arg_code_locations, $expected_shape, $actual_shape);
            ObjectPropertiesValidator::checkNonexistentProperties($actual_shape, $expected_shape, $arg_code_locations, $method_code_location);
            ObjectPropertiesValidator::checkMissingProperties($method_code_location, $expected_shape, $actual_shape);
        });
    }

    /**
     * @param Type\Union $shape_decoder_type
     * @return Option<array<string, Type\Union>>
     */
    private static function extractShapeFromDecoderTypeParam(Type\Union $shape_decoder_type): Option
    {
        return Option::do(function() use ($shape_decoder_type) {
            $atomics = asList($shape_decoder_type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $decoder = yield firstOf($atomics, Type\Atomic\TGenericObject::class);
            yield proveTrue(DecoderInterface::class === $decoder->value);
            yield proveTrue(1 === count($decoder->type_params));

            $decoder_type_param = yield first($decoder->type_params);

            $type_param_atomic = $decoder_type_param->getAtomicTypes();
            yield proveTrue(1 === count($type_param_atomic));

            $keyed_array = yield first($type_param_atomic);

            $extracted_shape = match (true) {
                ($keyed_array instanceof Type\Atomic\TArray) => [],
                ($keyed_array instanceof Type\Atomic\TKeyedArray) => self::remapKeys($keyed_array)
            };

            return yield Option::of($extracted_shape);
        });
    }

    /**
     * @return array<string, Type\Union>
     */
    private static function remapKeys(Type\Atomic\TKeyedArray $keyed_array): array
    {
        $properties = [];

        foreach ($keyed_array->properties as $property => $type) {
            $properties[(string) $property] = $type;
        }

        return $properties;
    }
}
