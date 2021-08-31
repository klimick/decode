<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder\ADT;

use Klimick\Decode\Decoder\SumType;
use Klimick\PsalmDecode\Issue\RuntimeData\InvalidSumTypeInstantiationIssue;
use PhpParser\Node;
use Fp\Functional\Option\Option;
use Klimick\PsalmDecode\Psalm;
use Psalm\CodeLocation;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Type;
use function Fp\Cast\asList;
use function Fp\Collection\first;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveString;

final class SumTypeNewAnalysis implements AfterExpressionAnalysisInterface
{
    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $new_expr = yield proveOf($event->getExpr(), Node\Expr\New_::class);

            $codebase = $event->getCodebase();
            $source = $event->getStatementsSource();

            $expected_case_type = yield proveOf($new_expr->class, Node\Name::class)
                ->flatMap(fn($name) => proveString($name->getAttribute('resolvedName')))
                ->filter(fn($class) => class_exists($class))
                ->filter(fn($class) => is_subclass_of($class, SumType::class))
                ->flatMap(fn($class) => RuntimeDecoder::getUnionCases($class))
                ->map(fn($case_types) => Type::combineUnionTypeArray(asList($case_types), $codebase));

            $actual_case_type = yield Option::some($new_expr->args)
                ->filter(fn($args) => 1 === count($args))
                ->flatMap(fn($args) => first($args))
                ->flatMap(fn($arg) => Psalm::getType($source->getNodeTypeProvider(), $arg->value));

            if (!UnionTypeComparator::isContainedBy($codebase, $actual_case_type, $expected_case_type)) {
                $code_location = new CodeLocation($source, $new_expr);
                $issue = new InvalidSumTypeInstantiationIssue($expected_case_type, $actual_case_type, $code_location);

                IssueBuffer::accepts($issue, $source->getSuppressedIssues());
            }
        });

        return null;
    }
}
