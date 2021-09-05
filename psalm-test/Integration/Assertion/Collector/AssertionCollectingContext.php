<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Klimick\PsalmTest\PsalmTest;
use PhpParser\Node\Expr\MethodCall;
use Psalm\CodeLocation;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;

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
}
