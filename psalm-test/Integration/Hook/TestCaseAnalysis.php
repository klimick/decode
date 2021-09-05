<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Hook;

use Fp\Collections\LinkedList;
use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use Klimick\PsalmTest\Integration\Assertion\AssertionsStorage;
use Klimick\PsalmTest\Integration\Assertion\Collector\AssertionCollectingContext;
use Klimick\PsalmTest\Integration\Assertion\Collector\HaveCodeAssertionCollector;
use Klimick\PsalmTest\Integration\Assertion\Collector\SeePsalmIssuesCollector;
use Klimick\PsalmTest\Integration\Assertion\Collector\SeeReturnTypeAssertionCollector;
use Klimick\PsalmTest\Integration\Assertion\Reconciler\SeePsalmIssuesAssertionReconciler;
use Klimick\PsalmTest\Integration\Assertion\Reconciler\SeeReturnTypeAssertionReconciler;
use Klimick\PsalmTest\Integration\Psalm;
use Klimick\PsalmTest\PsalmCodeBlockFactory;
use Klimick\PsalmTest\PsalmTest;
use Klimick\PsalmTest\StaticTestCase;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Psalm\Context;
use Psalm\IssueBuffer;
use Psalm\Plugin\EventHandler\AfterExpressionAnalysisInterface;
use Psalm\Plugin\EventHandler\AfterFunctionLikeAnalysisInterface;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Plugin\EventHandler\Event\AfterFunctionLikeAnalysisEvent;
use Psalm\Type\Atomic\TNamedObject;
use function Fp\Collection\second;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

/**
 * @psalm-type AssertionName = value-of<TestCaseAnalysis::SUPPORTED_ASSERTION_METHODS>
 */
final class TestCaseAnalysis implements AfterExpressionAnalysisInterface, AfterFunctionLikeAnalysisInterface
{
    private const ASSERTION_HAVE_CODE = 'haveCode';
    private const ASSERTION_SEE_RETURN_TYPE = 'seeReturnType';
    private const ASSERTION_SEE_PSALM_ISSUE_TYPE = 'seePsalmIssue';

    private const SUPPORTED_ASSERTION_METHODS = [
        self::ASSERTION_HAVE_CODE,
        self::ASSERTION_SEE_RETURN_TYPE,
        self::ASSERTION_SEE_PSALM_ISSUE_TYPE
    ];

    public static function afterExpressionAnalysis(AfterExpressionAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            $test_class = yield self::getTestClass($event->getContext());
            $test_method = yield self::getTestMethod($event->getContext());

            $assertion_call = yield proveOf($event->getExpr(), MethodCall::class);
            $assertion_name = yield self::getAssertionName($event, $assertion_call);

            $assertions = AssertionsStorage::get($test_class, $test_method);
            $assertion_context = new AssertionCollectingContext($test_class, $test_method, $assertion_name, $assertion_call, $event);

            AssertionsStorage::set(
                test_class: $test_class,
                test_method: $test_method,
                new_data: self::collectAssertions($assertions, $assertion_context),
            );
        });

        return null;
    }

    private static function collectAssertions(Assertions $assertions, AssertionCollectingContext $context): Assertions
    {
        $handlers = [
            HaveCodeAssertionCollector::class,
            SeeReturnTypeAssertionCollector::class,
            SeePsalmIssuesCollector::class,
        ];

        return LinkedList::collect($handlers)
            ->filter(fn($handler) => $handler::isSupported($context))
            ->fold($assertions, fn(Assertions $acc, $collector) => $collector::collect($acc, $context)->getOrCall(fn() => $acc));
    }

    /**
     * @psalm-return Option<AssertionName>
     */
    private static function getAssertionName(AfterExpressionAnalysisEvent $event, MethodCall $method_call): Option
    {
        return self::filterAssertionCall($event, $method_call)
            ->flatMap(fn($method_call) => proveOf($method_call->name, Identifier::class))
            ->map(fn($id) => $id->name)
            ->filter(fn($name) => in_array($name, self::SUPPORTED_ASSERTION_METHODS, true));
    }

    /**
     * @return Option<MethodCall>
     */
    private static function filterAssertionCall(AfterExpressionAnalysisEvent $event, MethodCall $method_call): Option
    {
        return Option::some($method_call->var)
            ->flatMap(Psalm::getType($event))
            ->flatMap(Psalm::asSingleAtomicOf(TNamedObject::class))
            ->filter(fn($a) => $a->value === StaticTestCase::class || $a->value === PsalmCodeBlockFactory::class)
            ->map(fn() => $method_call);
    }

    /**
     * @return Option<class-string<PsalmTest>>
     */
    private static function getTestClass(Context $context): Option
    {
        return Option::fromNullable($context->self)
            ->flatMap(Psalm::asSubclass(of: PsalmTest::class));
    }

    /**
     * @return Option<lowercase-string>
     */
    private static function getTestMethod(Context $context): Option
    {
        return Option::fromNullable($context->calling_method_id)
            ->map(fn($method_id) => explode('::', $method_id))
            ->flatMap(fn($method_id) => second($method_id));
    }

    public static function afterStatementAnalysis(AfterFunctionLikeAnalysisEvent $event): ?bool
    {
        Option::do(function() use ($event) {
            yield proveTrue($event->getStmt() instanceof ClassMethod);

            $test_class = yield self::getTestClass($event->getContext());
            $test_method = yield self::getTestMethod($event->getContext());

            $data = AssertionsStorage::get($test_class, $test_method);

            $handlers = [
                SeeReturnTypeAssertionReconciler::class,
                SeePsalmIssuesAssertionReconciler::class,
            ];

            $issues = [];

            foreach ($handlers as $handler) {
                $issue = $handler::reconcile($data)->get();

                if (null === $issue) {
                    continue;
                }

                $issues[] = $issue;
            }

            foreach ($issues as $issue) {
                IssueBuffer::accepts($issue);
            }
        });

        return null;
    }
}
