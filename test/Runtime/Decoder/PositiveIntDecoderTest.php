<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\positiveInt;
use function Klimick\Decode\Test\Helper\forAll;

final class PositiveIntDecoderTest extends TestCase
{
    public function testValidForAllPositiveIntegers(): void
    {
        forAll(Gen::positiveInt())->then(Check::thatValidFor(positiveInt()));
    }

    public function testInvalidForAllNotPositiveIntegers(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $value) => !is_int($value) || $value <= 0)
            ->then(Check::thatInvalidFor(positiveInt()));
    }
}
