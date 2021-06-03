<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\HighOrder;

use Klimick\PsalmDecode\DecodeIssue;
use PhpParser\Node;
use Psalm\Codebase;
use Psalm\CodeLocation;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;
use Psalm\IssueBuffer;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\NodeTypeProvider;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\AbstractDecoder;
use Fp\Functional\Option\Option;
use function Fp\Cast\asList;
use function Fp\Collection\map;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class ConstrainedContravariantCheckHandler implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [AbstractDecoder::class];
    }

    private static function withoutUndefined(Type\Union $type): Type\Union
    {
        if ($type->possibly_undefined) {
            $without_undefined = clone $type;
            $without_undefined->possibly_undefined = false;

            return $without_undefined;
        }

        return $type;
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Type\Union
    {
        Option::do(function() use ($event) {
            $source = $event->getSource();
            $codebase = $source->getCodebase();
            $type_provider = $source->getNodeTypeProvider();

            $args = yield self::getArgFromConstrainedMethod($event);

            $constraint_types = yield self::getConstraintTypes($args, $type_provider);
            $decoder_type_parameter = yield self::getDecoderTypeParameter($event);

            self::contravariantCheck(
                codebase: $codebase,
                source: $source,
                call_args: $args,
                constraint_types: $constraint_types,
                decoder_type_parameter: self::withoutUndefined($decoder_type_parameter),
            );
        });

        return null;
    }

    /**
     * @return Option<non-empty-list<Node\Arg>>
     */
    private static function getArgFromConstrainedMethod(MethodReturnTypeProviderEvent $event): Option
    {
        return Option::do(function() use ($event) {
            yield proveTrue('constrained' === $event->getMethodNameLowercase());

            $call_args = $event->getCallArgs();
            yield proveTrue(count($call_args) >= 1);

            return $call_args;
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
     * @param non-empty-list<Node\Arg> $args
     * @return Option<non-empty-list<Type\Union>>
     */
    private static function getConstraintTypes(array $args, NodeTypeProvider $type_provider): Option
    {
        return Option::do(function() use ($args, $type_provider) {
            $types = [];

            foreach ($args as $arg) {
                $constraint_type = yield Option::fromNullable($type_provider->getType($arg->value));
                $constraint_type_param = yield self::getTypeFromConstraintInterface($constraint_type);

                $types[] = self::literalTypeToNonLiteralType($constraint_type_param);
            }

            return $types;
        });
    }

    private static function literalTypeToNonLiteralType(Type\Union $type): Type\Union
    {
        $non_literal_atomics = array_values(
            map($type->getAtomicTypes(), fn(Type\Atomic $a) => self::literalAtomicToNonLiteralAtomic($a))
        );

        return new Type\Union($non_literal_atomics);
    }

    private static function literalAtomicToNonLiteralAtomic(Type\Atomic $a): Type\Atomic
    {
        return match (true) {
            $a instanceof Type\Atomic\TLiteralString,
                $a instanceof Type\Atomic\TLiteralClassString => new Type\Atomic\TString(),
            $a instanceof Type\Atomic\TLiteralInt => new Type\Atomic\TInt(),
            $a instanceof Type\Atomic\TLiteralFloat => new Type\Atomic\TFloat(),
            $a instanceof Type\Atomic\TKeyedArray => new Type\Atomic\TNonEmptyArray([
                self::literalTypeToNonLiteralType($a->getGenericKeyType()),
                self::literalTypeToNonLiteralType($a->getGenericValueType()),
            ]),
            default => $a,
        };
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

    /**
     * @param Codebase $codebase
     * @param StatementsSource $source
     * @param non-empty-list<Node\Arg> $call_args
     * @param non-empty-list<Type\Union> $constraint_types
     * @param Type\Union $decoder_type_parameter
     */
    private static function contravariantCheck(
        Codebase $codebase,
        StatementsSource $source,
        array $call_args,
        array $constraint_types,
        Type\Union $decoder_type_parameter,
    ): void
    {
        foreach ($constraint_types as $idx => $constraint_type) {
            if (UnionTypeComparator::isContainedBy($codebase, $decoder_type_parameter, $constraint_type)) {
                continue;
            }

            $code_location = new CodeLocation($source, $call_args[$idx]);
            $issue = DecodeIssue::incompatibleConstraints($constraint_type, $decoder_type_parameter, $code_location);

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }
    }
}
