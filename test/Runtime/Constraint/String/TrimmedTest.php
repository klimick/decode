<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint\String;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Runtime\Constraint\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\trimmed;
use function Klimick\Decode\Test\Helper\eris;

final class TrimmedTest extends TestCase
{
    public function testValid(): void
    {
        eris()
            ->forAll(Gen::nonEmptyString())
            ->when(fn(string $string) => $string === trim($string))
            ->then(fn(string $string) => Check::isValid()
                ->forConstraint(trimmed())
                ->withValue($string));
    }

    public function testInvalid(): void
    {
        eris()
            ->forAll(Gen::nonEmptyString())
            ->then(fn(string $string) => Check::isInvalid()
                ->forConstraint(trimmed())
                ->withValue(' ' . $string));
    }
}
