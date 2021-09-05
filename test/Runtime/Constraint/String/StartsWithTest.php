<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\String;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\startsWith;
use function Klimick\Decode\Test\Helper\eris;

final class StartsWithTest extends TestCase
{
    public function testValid(): void
    {
        eris()->forAll(Gen::nonEmptyString(), Gen::nonEmptyString())
            ->then(function(string $leftString, string $rightString) {
                /** @psalm-var non-empty-string $leftString */

                Check::isValid()
                    ->forConstraint(startsWith(string: $leftString))
                    ->withValue($leftString . $rightString);
            });
    }

    public function testInvalid(): void
    {
        eris()->forAll(Gen::nonEmptyString(), Gen::nonEmptyString())
            ->then(function(string $leftString, string $rightString) {
                /** @psalm-var non-empty-string $leftString */

                Check::isInvalid()
                    ->forConstraint(startsWith(string: $leftString))
                    ->withValue('never_' . $rightString . $leftString);
            });
    }
}
