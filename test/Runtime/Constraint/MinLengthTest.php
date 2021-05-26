<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\minLength;
use function Klimick\Decode\Test\Helper\eris;

final class MinLengthTest extends TestCase
{
    public function testValid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::nonEmptyString())
            ->then(function(string $actual) {
                /** @var positive-int $length */
                $length = mb_strlen($actual);

                Check::isValid()
                    ->forConstraint(minLength(is: $length))
                    ->withValue($actual);
            });
    }

    public function testInvalid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::string(), Gen::positiveInt())
            ->then(function(string $actual, int $delta) {
                /** @var positive-int $length */
                $length = mb_strlen($actual) + $delta;

                Check::isInvalid()
                    ->forConstraint(minLength(is: $length))
                    ->withValue($actual);
            });
    }
}
