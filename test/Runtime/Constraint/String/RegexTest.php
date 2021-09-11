<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\String;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\matchesRegex;
use function Klimick\Decode\Test\Helper\eris;

final class RegexTest extends TestCase
{
    private const NUMERIC_REGEX = '/^(\-)?[0-9]+(\.[0-9]+)?$/';

    public function testValid(): void
    {
        eris()->forAll(Gen::numericString())
            ->then(fn(string $numeric) => Check::isValid()
                ->forConstraint(matchesRegex(self::NUMERIC_REGEX))
                ->withValue($numeric));
    }

    public function testInvalid(): void
    {
        eris()->forAll(Gen::numericString())
            ->then(fn(string $numeric) => Check::isInvalid()
                ->forConstraint(matchesRegex(self::NUMERIC_REGEX))
                ->withValue("Number is {$numeric}"));
    }
}
