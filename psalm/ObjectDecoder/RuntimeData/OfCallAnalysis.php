<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\RuntimeData;

use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Report\DefaultReporter;
use Klimick\Decode\Report\ErrorReport;
use Klimick\PsalmDecode\DecodeIssue;
use PhpParser\Node;
use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\RuntimeData;
use Klimick\PsalmDecode\ObjectDecoder\GetGeneralParentClass;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\StatementsSource;
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

            $decoded = decode($value, $decoder)
                ->mapLeft(fn(Invalid $invalid) => DefaultReporter::report($invalid));

            if ($decoded->isLeft()) {
                $call_code_location = new CodeLocation($source, $method_call);
                $property_locations = yield self::getPropertyLocations($method_call, $source);

                /**
                 * @var ErrorReport $report
                 * @ignore-var
                 */
                $report = $decoded->get();

                self::reportIssues($report, $source, $call_code_location, $property_locations);
            }
        });

        return $analysis->get();
    }

    /**
     * @param array<string, CodeLocation> $property_locations
     */
    private static function reportIssues(
        ErrorReport $report,
        StatementsSource $source,
        CodeLocation $call_code_location,
        array $property_locations,
    ): void
    {
        foreach ($report->typeErrors as $error) {
            $actual_type = get_debug_type($error->actual);

            $issue = new DecodeIssue(
                message: implode(' ', [
                    "Wrong value at {$error->path}.",
                    "Expected type: {$error->expected}.",
                    "Actual type: {$actual_type}",
                ]),
                code_location: $property_locations[$error->path] ?? $call_code_location,
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }

        foreach ($report->constraintErrors as $error) {
            $payload = json_encode($error->payload);

            $issue = new DecodeIssue(
                message: "Constraint violation: {$error->constraint}. Payload: {$payload}",
                code_location: $property_locations[$error->path] ?? $call_code_location,
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }

        foreach ($report->undefinedErrors as $error) {
            $issue = new DecodeIssue(
                message: "Undefined property '{$error->property}' at path {$error->path}",
                code_location: $property_locations[$error->path] ?? $call_code_location,
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }
    }

    /**
     * @return Option<array<string, CodeLocation>>
     */
    private static function getPropertyLocations(Node\Expr\StaticCall $method_call, StatementsSource $source): Option
    {
        return Option::do(function() use ($method_call, $source) {
            yield proveTrue(1 === count($method_call->args));
            $array = yield proveOf($method_call->args[0]->value, Node\Expr\Array_::class);

            $result = [];
            self::buildCodeLocationMap($source, $array, $result);

            return $result;
        });
    }

    /**
     * @param array<string, CodeLocation> $flat
     */
    private static function buildCodeLocationMap(
        StatementsSource $source,
        Node\Expr\Array_ $array,
        array &$flat = [],
        int &$seq_key = 0,
        string $parentKey = '$',
    ): void
    {
        if ($parentKey !== '$') {
            $flat[$parentKey] = new CodeLocation($source, $array);
        }

        foreach ($array->items as $v) {
            if (null === $v) {
                continue;
            }

            if (null === $v->key) {
                $v->key = new Node\Scalar\LNumber($seq_key);
                $seq_key++;
            }

            if (!($v->key instanceof Node\Scalar\String_) && !($v->key instanceof Node\Scalar\LNumber)) {
                continue;
            }

            $currentKey = match (true) {
                $v->key instanceof Node\Scalar\String_ => ".{$v->key->value}",
                $v->key instanceof Node\Scalar\LNumber => "[{$v->key->value}]",
            };

            if ($v->value instanceof Node\Expr\Array_) {
                self::buildCodeLocationMap($source, $v->value, $flat, $seq_key, "{$parentKey}{$currentKey}");
            } else {
                $flat["{$parentKey}{$currentKey}"] = new CodeLocation($source, $v);
            }
        }
    }
}
