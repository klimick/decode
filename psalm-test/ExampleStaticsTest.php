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
                message: 'Argument 1 of #[self]::acceptInt expects #[option]<int>, #[option]<"100"> provided',
                args: [
                    'self' => self::class,
                    'option' => Option::class,
                ]
            )
            ->seePsalmIssue(
                type: 'InvalidScalarArgument',
                message: 'Argument 1 of #[self]::acceptInt expects #[option]<int>, #[option]<"200"> provided',
                args: [
                    'self' => self::class,
                    'option' => Option::class,
                ]
            );
    }
}
