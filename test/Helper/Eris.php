<?php

namespace Klimick\Decode\Test\Helper;

use Eris;

/**
 * @no-named-arguments
 */
function forAll(Eris\Generator ...$generators): Eris\Quantifier\ForAll
{
    return (new Eris\Facade())->forAll(...$generators);
}
