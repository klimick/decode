<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Helper\Check;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\arrKey;
use function Klimick\Decode\Test\Helper\forAll;

final class ArrKeyDecoderTest extends TestCase
{
    public function testValidForAllArrKeys(): void
    {
        forAll(Gen::arrKey())->then(Check::thatValidFor(arrKey()));
    }

    public function testInvalidForAllNotArrKeys(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $value) => !is_int($value))
            ->when(fn(mixed $value) => !is_string($value))
            ->then(Check::thatInvalidFor(arrKey()));
    }
}
