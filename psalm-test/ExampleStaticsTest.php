<?php

declare(strict_types=1);

namespace Klimick\PsalmTest;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\StaticType\StaticTypes;

final class ExampleStaticsTest extends PsalmTest
{
    /**
     * @param Option<int> $_param
     */
    public function acceptInt(Option $_param): int {
        return 0;
    }

    public function test(): void
    {
        StaticTestCase::describe()
            ->haveCode(function() {
                self::acceptInt(Option::some('100'));
                self::acceptInt(Option::some('200'));

                return 0;
            })
            ->seeReturnType(StaticTypes::int(), invariant: false)
            ->seePsalmIssue(
                type: 'InvalidScalarArgument',
                message: 'Argument 1 of Klimick\PsalmTest\ExampleStaticsTest::acceptInt expects Fp\Functional\Option\Option<int>, Fp\Functional\Option\Option<"100"> provided',
            )
            ->seePsalmIssue(
                type: 'InvalidScalarArgument',
                message: 'Argument 1 of Klimick\PsalmTest\ExampleStaticsTest::acceptInt expects Fp\Functional\Option\Option<int>, Fp\Functional\Option\Option<"200"> provided',
            );
    }
}
