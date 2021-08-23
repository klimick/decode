<?php

declare(strict_types=1);

namespace Klimick\PsalmTest;

use Klimick\PsalmTest\StaticType\StaticTypes as t;

final class ExampleStaticsTest extends PsalmTest
{
    public function test(): void
    {
        StaticTestCase::describe()
            ->haveCode(fn() => 1 + 1)
            ->seeReturnType(t::int(), invariant: false);

        StaticTestCase::describe()
            ->haveCode(fn() => 1 + 1)
            ->seeReturnType(t::int());
    }
}
