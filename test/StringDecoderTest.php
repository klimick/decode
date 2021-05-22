<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\Typed as t;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Test\Helper\forAll;

final class StringDecoderTest extends TestCase
{
    public function testValidForAllStrings(): void
    {
        forAll(Gen::string())->then(Check::thatValidFor(t::string));
    }

    public function testInvalidForAllNotStrings(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $value) => !is_string($value))
            ->then(Check::thatInvalidFor(t::string));
    }
}
