<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\Typed as t;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Test\Helper\forAll;

final class NumericStringDecoderTest extends TestCase
{
    public function testValidForAllNumericStrings(): void
    {
        forAll(Gen::numericString())->then(Check::thatValidFor(t::numericString));
    }

    public function testInvalidForAllNotNumericStrings(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $value) => !is_string($value))
            ->then(Check::thatInvalidFor(t::numericString));
    }
}
