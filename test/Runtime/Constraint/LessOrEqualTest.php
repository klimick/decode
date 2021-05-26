<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\lessOrEqual;
use function Klimick\Decode\Test\Helper\eris;

final class LessOrEqualTest extends TestCase
{
    public function testValid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int(), Gen::int())
            ->when(fn(int $actual, int $expected) => $actual <= $expected)
            ->then(fn(int $actual, int $expected) => Check::isValid()
                ->forConstraint(lessOrEqual(to: $expected))
                ->withValue($actual));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float(), Gen::float())
            ->when(fn(float $actual, float $expected) => $actual <= $expected)
            ->then(fn(float $actual, float $expected) => Check::isValid()
                ->forConstraint(lessOrEqual(to: $expected))
                ->withValue($actual));
    }

    public function testInvalid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int(), Gen::int())
            ->when(fn(int $actual, int $expected) => $actual > $expected)
            ->then(fn(int $actual, int $expected) => Check::isInvalid()
                ->forConstraint(lessOrEqual(to: $expected))
                ->withValue($actual));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float(), Gen::float())
            ->when(fn(float $actual, float $expected) => $actual > $expected)
            ->then(fn(float $actual, float $expected) => Check::isInvalid()
                ->forConstraint(lessOrEqual(to: $expected))
                ->withValue($actual));
    }
}
