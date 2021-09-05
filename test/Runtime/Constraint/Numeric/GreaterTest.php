<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\Numeric;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\greater;
use function Klimick\Decode\Test\Helper\eris;

final class GreaterTest extends TestCase
{
    public function testValid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int(), Gen::elements(0.1, 1))
            ->then(fn(int $num, int|float $zeroOrOne) => Check::isValid()
                ->forConstraint(greater(than: $num))
                ->withValue($num + $zeroOrOne));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float(), Gen::elements(0.1, 1))
            ->then(fn(float $num, int|float $zeroOrOne) => Check::isValid()
                ->forConstraint(greater(than: $num))
                ->withValue($num + $zeroOrOne));
    }

    public function testInvalid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int(), Gen::elements(0, 0.1, 1))
            ->then(fn(int $num, int|float $zeroOrOne) => Check::isInvalid()
                ->forConstraint(greater(than: $num))
                ->withValue($num - $zeroOrOne));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float(), Gen::elements(0, 0.1, 1))
            ->then(fn(float $num, int|float $zeroOrOne) => Check::isInvalid()
                ->forConstraint(greater(than: $num))
                ->withValue($num - $zeroOrOne));
    }
}
