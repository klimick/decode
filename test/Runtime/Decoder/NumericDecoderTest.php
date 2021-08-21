<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\numeric;
use function Klimick\Decode\Test\Helper\forAll;

final class NumericDecoderTest extends TestCase
{
    public function testValidForAllNumerics(): void
    {
        forAll(Gen::numeric())->then(Check::thatValidFor(numeric()));
    }

    public function testInvalidForAllNotNumerics(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $value) => !is_numeric($value))
            ->then(Check::thatInvalidFor(numeric()));
    }
}
