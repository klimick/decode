<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Klimick\Decode\Invalid;
use Klimick\PsalmDecode\DecodeIssue;
use PhpParser\Node;
use Fp\Functional\Option\Option;
use Klimick\Decode\RuntimeData;
use Klimick\PsalmDecode\ObjectDecoder\GetGeneralParentClass;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;
use function Fp\Evidence\proveTrue;
use function Klimick\Decode\decode;

final class OfCallAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        $analysis = Option::do(function() use ($event) {
            $codebase = $event->getCodebase();
            $source = $event->getStatementsSource();
            $type_provider = $source->getNodeTypeProvider();

            $method_call = yield proveOf($event->getExpr(), Node\Expr\StaticCall::class);
            $method_identifier = yield proveOf($method_call->name, Node\Identifier::class);

            yield proveTrue('of' === $method_identifier->name);
            yield proveTrue(1 === count($method_call->args));

            $class_node = yield proveOf($method_call->class, Node\Name::class);
            $class_string = yield proveString($class_node->getAttribute('resolvedName'));

            $general_class = yield GetGeneralParentClass::for($class_string, $codebase);
            yield proveTrue(RuntimeData::class === $general_class);

            $arg_type = yield Option::of($type_provider->getType($method_call->args[0]->value));

            $value = LiteralKeyedArray::toPhpValue($arg_type)->getOrElse(
                DecodeIssue::couldNotAnalyzeOfCall(new CodeLocation($source, $method_call))
            );

            if ($value instanceof DecodeIssue) {
                IssueBuffer::accepts($value, $source->getSuppressedIssues());
                return;
            }

            $decoder = yield RuntimeDecoder::instance($class_string);
            $decoded = decode($decoder, $value);

            if ($decoded->isLeft()) {
                /**
                 * @var Invalid $invalid
                 * @ignore-var
                 */
                $invalid = $decoded->get();

                $issue = DecodeIssue::couldNotDecodeRuntimeData($invalid, new CodeLocation($source, $method_call));
                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        });

        return $analysis->get();
    }
}