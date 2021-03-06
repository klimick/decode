<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis;

use Fp\Functional\Option\Option;
use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\PsalmDecode\Issue;
use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use Psalm\Type\Atomic\TLiteralString;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class DecoderFromAfterMethodCallAnalysis implements AfterMethodCallAnalysisInterface
{
    public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        Option::do(function() use ($event) {
            $method_call = yield proveTrue(DecoderInterface::class . '::' . 'from' === $event->getAppearingMethodId())
                ->flatMap(fn() => proveOf($event->getExpr(), MethodCall::class))
                ->filter(fn(MethodCall $call) => !$call->isFirstClassCallable());

            foreach ($method_call->getArgs() as $arg) {
                $is_valid_literal = PsalmApi::$types->getType($event, $arg->value)
                    ->flatMap(fn($type) => PsalmApi::$types->asSingleAtomicOf(TLiteralString::class, $type))
                    ->map(fn($literal) => $literal->value)
                    ->fold(
                        ifSome: fn($arg) => '$' === $arg || (str_starts_with($arg, '$.') && mb_strlen($arg) > 2),
                        ifNone: fn() => false,
                    );

                if (!$is_valid_literal) {
                    $source = $event->getStatementsSource();

                    $issue = new Issue\InvalidPropertyAlias(new CodeLocation($source, $method_call->name));
                    IssueBuffer::accepts($issue, $source->getSuppressedIssues());
                }
            }
        });
    }
}
