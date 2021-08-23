<?php

declare(strict_types=1);

namespace Klimick\PsalmTest;

use Closure;

final class PsalmCodeBlockFactory
{
    public function haveCode(Closure $codeBlock): StaticTestCase
    {
        return new StaticTestCase($codeBlock);
    }
}
