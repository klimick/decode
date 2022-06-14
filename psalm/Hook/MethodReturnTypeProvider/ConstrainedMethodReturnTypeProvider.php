<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\MethodReturnTypeProvider;

use Fp\Collections\NonEmptyArrayList;
use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\CallArg;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\PsalmDecode\Common\DecoderType;
use Klimick\PsalmDecode\Issue;
use PhpParser\Node\Expr\MethodCall;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\StatementsSource;
use Psalm\Storage\FunctionLikeParameter;
use Psalm\Type\Atomic\TClosure;
use Psalm\Type\Atomic\TGenericObject;
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
                    ->flatMap(fn($atomic) => DecoderType::getGeneric($atomic)),
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
                ->map(fn($type_param) => PsalmApi::$types->asNonLiteralType($type_param))
                ->map(fn($type) => new CallArg($call_arg->node, $call_arg->location, $type))
        );
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
        $expected_constraint_as_closure = new Union([
            new TClosure(
                params: [new FunctionLikeParameter(name: '_', by_ref: false, type: $decoder_type_param)],
            ),
        ]);

        foreach ($constrained_call_args as $offset => $call_arg) {
            $actual_constraint_as_closure = new Union([
                new TClosure(
                    params: [new FunctionLikeParameter(name: '_', by_ref: false, type: $call_arg->type)],
                ),
            ]);

            if (PsalmApi::$types->isTypeContainedByType($actual_constraint_as_closure, $expected_constraint_as_closure)) {
                continue;
            }

            IssueBuffer::accepts(
                new Issue\IncompatibleConstraint(
                    arg_offset: $offset + 1,
                    expected: new Union([
                        new TGenericObject(ConstraintInterface::class, [$decoder_type_param]),
                    ]),
                    actual: new Union([
                        new TGenericObject(ConstraintInterface::class, [$call_arg->type]),
                    ]),
                    code_location: $call_arg->location,
                ),
                $source->getSuppressedIssues(),
            );
        }
    }
}
