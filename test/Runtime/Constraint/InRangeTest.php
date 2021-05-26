<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\inRange;
use function Klimick\Decode\Test\Helper\eris;

final class InRangeTest extends TestCase
{
    public function testValid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int())
            ->then(fn(int $actual) => Check::isValid()
                ->forConstraint(inRange(from: $actual, to: $actual))
                ->withValue($actual));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int(), Gen::positiveInt())
            ->then(fn(int $actual, int $delta) => Check::isValid()
                ->forConstraint(inRange(from: $actual - $delta, to: $actual + $delta))
                ->withValue($actual));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float())
            ->then(fn(float $actual) => Check::isValid()
                ->forConstraint(inRange(from: $actual, to: $actual))
                ->withValue($actual));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float(), Gen::positiveInt())
            ->then(fn(float $actual, int $delta) => Check::isValid()
                ->forConstraint(inRange(from: $actual - $delta, to: $actual + $delta))
                ->withValue($actual));
    }

    public function testInvalid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int(), Gen::positiveInt())
            ->then(fn(int $actual, int $delta) => Check::isInvalid()
                ->forConstraint(inRange(from: $actual + $delta, to: $actual - $delta))
                ->withValue($actual));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float(), Gen::positiveInt())
            ->then(fn(float $actual, int $delta) => Check::isInvalid()
                ->forConstraint(inRange(from: $actual + $delta, to: $actual - $delta))
                ->withValue($actual));
    }
}
