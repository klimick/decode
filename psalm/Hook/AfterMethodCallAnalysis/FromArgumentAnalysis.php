<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterMethodCallAnalysis;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\PsalmDecode\Issue\HighOrder\InvalidPropertyAliasIssue;
use Klimick\PsalmTest\Integration\Psalm;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use Psalm\Type\Atomic\TLiteralString;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class FromArgumentAnalysis implements AfterMethodCallAnalysisInterface
{
    public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        Option::do(function() use ($event) {
            $method_call = yield proveTrue(DecoderInterface::class . '::' . 'from' === $event->getAppearingMethodId())
                ->flatMap(fn() => proveOf($event->getExpr(), MethodCall::class));

            $is_valid_literal = firstOf($method_call->args, Arg::class)
                ->flatMap(fn($arg) => Psalm::getType($event, $arg->value))
                ->flatMap(fn($type) => Psalm::asSingleAtomicOf(TLiteralString::class, $type))
                ->map(fn($literal) => $literal->value)
                ->fold(
                    ifSome: fn($arg) => '$' === $arg || (str_starts_with($arg, '$.') && mb_strlen($arg) > 2),
                    ifNone: fn() => false,
                );

            if (!$is_valid_literal) {
                $source = $event->getStatementsSource();

                $issue = new InvalidPropertyAliasIssue(new CodeLocation($source, $method_call->name));
                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        });
    }
}
