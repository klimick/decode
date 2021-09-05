<?php

declare(strict_types=1);

namespace Klimick\PsalmTest;

use Closure;

/**
 * @template TTestCaseName of non-empty-string
 */
final class PsalmCodeBlockFactory
{
    /**
     * @return StaticTestCase<TTestCaseName>
     */
    public function haveCode(Closure $codeBlock): StaticTestCase
    {
        NoCode::here();
    }
}
