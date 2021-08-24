<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\PsalmTest;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Type;
use function Fp\Cast\asList;
use function Fp\Collection\first;

final class AssertionCollectingContext
{
    /**
     * @param class-string<PsalmTest> $test_class
     * @param lowercase-string $test_method
     */
    public function __construct(
        public string $test_class,
        public string $test_method,
        public string $assertion_name,
        public MethodCall $assertion_call,
        public AfterExpressionAnalysisEvent $event
    ) {}

    public function getCodeLocation(): CodeLocation
    {
        return new CodeLocation($this->event->getStatementsSource(), $this->assertion_call);
    }

    /**
     * @param Node\Expr | Node\Name | Node\Stmt\Return_ $node
     * @return Option<Type\Union>
     */
    public function getType(Node $node): Option
    {
        $type_provider = $this->event
            ->getStatementsSource()
            ->getNodeTypeProvider();

        return Option::fromNullable($type_provider->getType($node));
    }

    /**
     * @param Node\Expr | Node\Name | Node\Stmt\Return_ $node
     * @return Option<Type\Atomic>
     */
    public function getSingleAtomicType(Node $node): Option
    {
        return $this->getType($node)
            ->map(fn($type) => asList($type->getAtomicTypes()))
            ->filter(fn($atomics) => 1 === count($atomics))
            ->flatMap(fn($atomics) => first($atomics));
    }
}
