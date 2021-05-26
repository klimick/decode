<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\equal;
use function Klimick\Decode\Test\Helper\eris;

final class EqualTest extends TestCase
{
    public function testValid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int())
            ->then(fn(int $actual) => Check::isValid()
                ->forConstraint(equal(to: $actual))
                ->withValue($actual));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float())
            ->then(fn(float $actual) => Check::isValid()
                ->forConstraint(equal(to: $actual))
                ->withValue($actual));
    }

    public function testInvalid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::int(), Gen::positiveInt())
            ->then(fn(int $actual, int $delta) => Check::isInvalid()
                ->forConstraint(equal(to: $actual + $delta))
                ->withValue($actual));

        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::float(), Gen::positiveInt())
            ->then(fn(float $actual, int $delta) => Check::isInvalid()
                ->forConstraint(equal(to: $actual + $delta))
                ->withValue($actual));
    }
}
