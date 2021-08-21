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

function eris(int $repeat = null, int $ratio = null): ErisFacade
{
    return new ErisFacade($repeat, $ratio);
}

function anyValue(): mixed
{
    return null;
}
