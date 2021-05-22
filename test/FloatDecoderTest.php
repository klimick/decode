<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\Typed as t;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Test\Helper\forAll;

final class FloatDecoderTest extends TestCase
{
    public function testValidForAllFloats(): void
    {
        forAll(Gen::float())->then(Check::thatValidFor(t::float));
    }

    public function testInvalidForAllNotFloats(): void
    {
        forAll(Gen::scalar())
            ->when(fn(mixed $value) => !is_float($value))
            ->then(Check::thatInvalidFor(t::float));
    }
}
