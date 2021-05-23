<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Constrain;

use Klimick\PsalmDecode\DecodeIssue;
use PhpParser\Node;
use Psalm\IssueBuffer;
use Psalm\Type;
use Psalm\Codebase;
use Psalm\NodeTypeProvider;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;
use Klimick\Decode\Internal\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder;
use Fp\Functional\Option\Option;
use function Fp\Cast\asList;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class ConstrainedContravariantCheckHandler implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [Decoder::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Type\Union
    {
        Option::do(function() use ($event) {
            $source = $event->getSource();
            $codebase = $source->getCodebase();
            $type_provider = $source->getNodeTypeProvider();

            $constraints_type = yield self::getArgFromConstrainedMethod($event)
                ->flatMap(fn($arg) => self::getConstraintsType($type_provider, $codebase, $arg));

            $decoder_type_parameter = yield self::getDecoderTypeParameter($event);

            if (!UnionTypeComparator::isContainedBy($codebase, $decoder_type_parameter, $constraints_type)) {
                $issue = DecodeIssue::incompatibleConstraints($constraints_type, $decoder_type_parameter, $event->getCodeLocation());
                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        });

        return null;
    }

    /**
     * @return Option<Node\Arg>
     */
    private static function getArgFromConstrainedMethod(MethodReturnTypeProviderEvent $event): Option
    {
        return Option::do(function() use ($event) {
            yield proveTrue('constrained' === $event->getMethodNameLowercase());

            $call_args = $event->getCallArgs();
            yield proveTrue(1 === count($call_args));

            return $call_args[0];
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function getTypeFromConstraintInterface(Type\Union $type): Option
    {
        return Option::do(function() use ($type) {
            $atomics = asList($type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $generic_object = yield proveOf($atomics[0], Type\Atomic\TGenericObject::class);
            yield proveTrue($generic_object->value === ConstraintInterface::class);
            yield proveTrue(1 === count($generic_object->type_params));

            return $generic_object->type_params[0];
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function getTypeFromKeyedArray(Type\Atomic\TKeyedArray $keyed_array, Codebase $codebase): Option
    {
        return Option::do(function() use ($keyed_array, $codebase) {
            yield proveTrue($keyed_array->is_list);

            $types = [];

            foreach ($keyed_array->properties as $type) {
                $types[] = yield self::getTypeFromConstraintInterface($type);
            }

            return Type::combineUnionTypeArray($types, $codebase);
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function getTypeFromNonEmptyList(Type\Atomic\TNonEmptyList $non_empty_list): Option
    {
        return self::getTypeFromConstraintInterface($non_empty_list->type_param);
    }

    /**
     * @return Option<Type\Union>
     */
    private static function getConstraintsType(NodeTypeProvider $type_provider, Codebase $codebase, Node\Arg $arg): Option
    {
        return Option::do(function() use ($type_provider, $codebase, $arg) {
            $type = yield Option::of($type_provider->getType($arg->value));

            $atomics = asList($type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            return yield match (true) {
                $atomics[0] instanceof Type\Atomic\TKeyedArray => self::getTypeFromKeyedArray($atomics[0], $codebase),
                $atomics[0] instanceof Type\Atomic\TNonEmptyList => self::getTypeFromNonEmptyList($atomics[0]),
                default => Option::none(),
            };
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function getDecoderTypeParameter(MethodReturnTypeProviderEvent $event): Option
    {
        return Option::do(function() use ($event) {
            $type_parameters = asList($event->getTemplateTypeParameters() ?? []);
            yield proveTrue(1 === count($type_parameters));

            return $type_parameters[0];
        });
    }
}
