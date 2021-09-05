<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\String;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\endsWith;
use function Klimick\Decode\Test\Helper\eris;

final class EndsWithTest extends TestCase
{
    public function testValid(): void
    {
        eris()->forAll(Gen::nonEmptyString(), Gen::nonEmptyString())
            ->then(function(string $leftString, string $rightString) {
                /** @psalm-var non-empty-string $rightString */

                Check::isValid()
                    ->forConstraint(endsWith(string: $rightString))
                    ->withValue($leftString . $rightString);
            });
    }

    public function testInvalid(): void
    {
        eris()->forAll(Gen::nonEmptyString(), Gen::nonEmptyString())
            ->then(function(string $leftString, string $rightString) {
                /** @psalm-var non-empty-string $rightString */

                Check::isInvalid()
                    ->forConstraint(endsWith(string: $rightString))
                    ->withValue($rightString . $leftString . '_never');
            });
    }
}
