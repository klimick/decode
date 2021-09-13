<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\HighOrder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\PsalmDecode\Issue\HighOrder\InvalidPropertyAliasIssue;
use Klimick\PsalmDecode\Psalm;
use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterMethodCallAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterMethodCallAnalysisEvent;
use Psalm\Type\Atomic\TLiteralString;
use function Fp\Cast\asList;
use function Fp\Collection\firstOf;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

final class FromArgumentAnalysis implements AfterMethodCallAnalysisInterface
{
    public static function afterMethodCallAnalysis(AfterMethodCallAnalysisEvent $event): void
    {
        Option::do(function() use ($event) {
            $source = $event->getStatementsSource();
            $type_provider = $source->getNodeTypeProvider();

            $method_id = explode('::', $event->getAppearingMethodId());

            yield proveTrue(2 === count($method_id));
            [$class_name, $method_name] = $method_id;

            yield proveTrue($class_name === DecoderInterface::class);
            yield proveTrue($method_name === 'from');

            $method_call = yield proveOf($event->getExpr(), MethodCall::class);

            yield proveTrue(1 === count($method_call->args));

            $type = yield Psalm::getType($type_provider, $method_call->args[0]->value);

            $atomics = asList($type->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            $literal_string = firstOf($atomics, TLiteralString::class)
                ->map(fn($literal) => $literal->value)
                ->get();

            if (!self::isValidArgument($literal_string)) {
                $issue = new InvalidPropertyAliasIssue(new CodeLocation($source, $method_call->name));
                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        });
    }

    private static function isValidArgument(?string $argument): bool
    {
        if (null === $argument) {
            return false;
        }

        if ('$' === $argument) {
            return true;
        }

        return str_starts_with($argument, '$.') && mb_strlen($argument) > 2;
    }
}
