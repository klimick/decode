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
                    ->flatMap(fn($atomic) => DecoderType::getDecoderGeneric($atomic))
                    ->map(fn($type) => PsalmApi::$types->asAlwaysDefined($type)),
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
        foreach ($constrained_call_args as $call_arg) {
            if (PsalmApi::$types->isTypeContainedByType($decoder_type_param, $call_arg->type)) {
                continue;
            }

            IssueBuffer::accepts(
                new IncompatibleConstraintIssue($call_arg->type, $decoder_type_param, $call_arg->location),
                $source->getSuppressedIssues(),
            );
        }
    }
}
