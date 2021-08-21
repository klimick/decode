<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Test\Helper\forAll;

final class BoolDecoderTest extends TestCase
{
    public function testValidForAllBooleans(): void
    {
        forAll(Gen::bool())
            ->then(Check::thatValidFor(bool()));
    }

    public function testInvalidForAllNotBooleans(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $value) => !is_bool($value))
            ->then(Check::thatInvalidFor(bool()));
    }
}
