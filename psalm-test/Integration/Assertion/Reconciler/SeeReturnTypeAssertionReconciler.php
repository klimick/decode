<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Reconciler;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use Klimick\PsalmTest\Integration\Assertion\Collector\HaveCodeAssertionData;
use Klimick\PsalmTest\Integration\Assertion\Collector\SeeReturnTypeAssertionData;
use Klimick\PsalmTest\Integration\Assertion\Issue\SeeReturnTypeAssertionFailed;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;
use Psalm\Internal\Analyzer\ProjectAnalyzer;
use Psalm\Type;

final class SeeReturnTypeAssertionReconciler implements AssertionReconcilerInterface
{
    public static function reconcile(Assertions $data): Option
    {
        return Option::do(function() use ($data) {
            $haveCodeAssertion = yield $data(HaveCodeAssertionData::class);
            $seeReturnTypeAssertion = yield $data(SeeReturnTypeAssertionData::class);

            $isValid = self::isValid(
                expected: $seeReturnTypeAssertion->expected_return_type,
                actual: $haveCodeAssertion->actual_return_type,
                invariant: $seeReturnTypeAssertion->invariant_compare,
            );

            return yield !$isValid
                ? Option::some(new SeeReturnTypeAssertionFailed($haveCodeAssertion, $seeReturnTypeAssertion))
                : Option::none();
        });
    }

    private static function isValid(Type\Union $expected, Type\Union $actual, bool $invariant): bool
    {
        if ($invariant) {
            return $expected->equals($actual);
        }

        return UnionTypeComparator::isContainedBy(
            codebase: ProjectAnalyzer::$instance->getCodebase(),
            input_type: $actual,
            container_type: $expected,
        );
    }
}
