<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\MethodReturnTypeProvider;

use Fp\Collections\NonEmptyArrayList;
use Fp\Functional\Option\Option;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\PsalmDecode\Helper\DecoderType;
use Klimick\PsalmDecode\Issue\HighOrder\IncompatibleConstraintIssue;
use Fp\PsalmToolkit\Toolkit\CallArg;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use PhpParser\Node\Expr\MethodCall;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\StatementsSource;
use Psalm\Type\Atomic\TArray;
use Psalm\Type\Atomic\TFloat;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TInt;
use Psalm\Type\Atomic\TKeyedArray;
use Psalm\Type\Atomic\TList;
use Psalm\Type\Atomic\TLiteralClassString;
use Psalm\Type\Atomic\TLiteralFloat;
use Psalm\Type\Atomic\TLiteralInt;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Atomic\TNonEmptyArray;
use Psalm\Type\Atomic\TNonEmptyList;
use Psalm\Type\Atomic\TNonEmptyString;
use Psalm\Type\Atomic\TString;
use Psalm\Type\Union;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class ConstrainedMethodReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [DecoderInterface::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Union
    {
        Option::do(function() use ($event) {
            yield proveTrue('constrained' === $event->getMethodNameLowercase());

            self::contravariantCheck(
                source: $event->getSource(),
                constrained_call_args: yield PsalmApi::$args->getNonEmptyCallArgs($event)
                    ->flatMap(fn($call_args) => self::mapCallArgs($call_args)),
                decoder_type_param: yield proveOf($event->getStmt(), MethodCall::class)
                    ->flatMap(fn($method_call) => PsalmApi::$types->getType($event, $method_call->var))
                    ->flatMap(fn($atomic) => DecoderType::extractTypeParam($atomic))
                    ->map(fn($type) => self::withoutUndefined($type)),
            );
        });

        return null;
    }

    /**
     * @param NonEmptyArrayList<CallArg> $call_args
     * @return Option<NonEmptyArrayList<CallArg>>
     */
    private static function mapCallArgs(NonEmptyArrayList $call_args): Option
    {
        return $call_args->everyMap(
            fn(CallArg $call_arg) => PsalmApi::$types->asSingleAtomicOf(TGenericObject::class, $call_arg->type)
                ->flatMap(fn($object) => PsalmApi::$types->getFirstGeneric($object, ConstraintInterface::class))
                ->map(fn($type_param) => self::toNonLiteralType($type_param))
                ->map(fn($type) => new CallArg($call_arg->node, $call_arg->location, $type))
        );
    }

    private static function toNonLiteralType(Union $type): Union
    {
        return new Union(
            NonEmptyArrayList::collectNonEmpty($type->getAtomicTypes())
                ->map(fn($a) => match (true) {
                    $a instanceof TLiteralClassString => new TNonEmptyString(),
                    $a instanceof TLiteralString => empty($a->value)
                        ? new TString()
                        : new TNonEmptyString(),
                    $a instanceof TLiteralInt => new TInt(),
                    $a instanceof TLiteralFloat => new TFloat(),
                    $a instanceof TKeyedArray => new TNonEmptyArray([
                        self::toNonLiteralType($a->getGenericKeyType()),
                        self::toNonLiteralType($a->getGenericValueType()),
                    ]),
                    $a instanceof TNonEmptyList => new TNonEmptyList(
                        self::toNonLiteralType($a->type_param),
                    ),
                    $a instanceof TList => new TList(
                        self::toNonLiteralType($a->type_param),
                    ),
                    $a instanceof TNonEmptyArray => new TNonEmptyArray([
                        self::toNonLiteralType($a->type_params[0]),
                        self::toNonLiteralType($a->type_params[1]),
                    ]),
                    $a instanceof TArray => new TArray([
                        self::toNonLiteralType($a->type_params[0]),
                        self::toNonLiteralType($a->type_params[1]),
                    ]),
                    default => $a,
                })
                ->toArray()
        );
    }

    private static function withoutUndefined(Union $type): Union
    {
        if ($type->possibly_undefined) {
            $without_undefined = clone $type;
            $without_undefined->possibly_undefined = false;

            return $without_undefined;
        }

        return $type;
    }

    /**
     * @param NonEmptyArrayList<CallArg> $constrained_call_args
     */
    private static function contravariantCheck(
        StatementsSource $source,
        NonEmptyArrayList $constrained_call_args,
        Union $decoder_type_param,
    ): void
    {
        $codebase = $source->getCodebase();

        foreach ($constrained_call_args as $call_arg) {
            $is_contained_by = $codebase->isTypeContainedByType(
                input_type: $decoder_type_param,
                container_type: $call_arg->type,
            );

            if ($is_contained_by) {
                continue;
            }

            IssueBuffer::accepts(
                new IncompatibleConstraintIssue($call_arg->type, $decoder_type_param, $call_arg->location),
                $source->getSuppressedIssues(),
            );
        }
    }
}
