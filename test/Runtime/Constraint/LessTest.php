<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\less;
use function Klimick\Decode\Test\Helper\eris;

final class LessTest extends TestCase
{
    public function testValid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int())
            ->then(fn(int $num) => Check::isValid()
                ->forConstraint(less(than: $num))
                ->withValue($num - 1));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float())
            ->then(fn(float $num) => Check::isValid()
                ->forConstraint(less(than: $num))
                ->withValue($num - 1));
    }

    public function testInvalid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int(), Gen::elements(0, 0.1, 1))
            ->then(fn(int $num, int $zeroOrOne) => Check::isInvalid()
                ->forConstraint(less(than: $num))
                ->withValue($num + $zeroOrOne));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float(), Gen::elements(0, 0.1, 1))
            ->then(fn(float $num, float $zeroOrOne) => Check::isInvalid()
                ->forConstraint(less(than: $num))
                ->withValue($num + $zeroOrOne));
    }
}
