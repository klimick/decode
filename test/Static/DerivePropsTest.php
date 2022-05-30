<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static;

use Klimick\Decode\Test\Static\Fixtures\User;
use Fp\PsalmToolkit\StaticTest\PsalmTest;
use Fp\PsalmToolkit\StaticTest\StaticTestCase;
use Fp\PsalmToolkit\StaticType\StaticTypes as t;

final class DerivePropsTest extends PsalmTest
{
    public function testDerivation(): void
    {
        StaticTestCase::describe('Prop types will be inferred')
            ->haveCode(function(User $u) {
                return $u->name;
            })
            ->seeReturnType(t::string());
    }
}
