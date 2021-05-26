<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\maxLength;
use function Klimick\Decode\Test\Helper\eris;

final class MaxLengthTest extends TestCase
{
    public function testValid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::nonEmptyString())
            ->then(function(string $actual) {
                /** @var positive-int $length */
                $length = mb_strlen($actual);

                Check::isValid()
                    ->forConstraint(maxLength(is: $length))
                    ->withValue($actual);
            });
    }

    public function testInvalid(): void
    {
        eris(repeat: 1000, ratio: 40)
            ->forAll(Gen::nonEmptyString())
            ->then(function(string $actual) {
                /** @var positive-int $length */
                $length = mb_strlen($actual);

                Check::isInvalid()
                    ->forConstraint(maxLength(is: $length))
                    ->withValue($actual . $actual);
            });
    }
}
