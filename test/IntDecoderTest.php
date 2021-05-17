<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\t;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Test\Helper\forAll;

final class IntDecoderTest extends TestCase
{
    public function testValidForAllIntegers(): void
    {
        forAll(Gen::int())->then(Check::thatValidFor(t::int));
    }

    public function testInvalidForAllNotIntegers(): void
    {
        forAll(Gen::scalar())
            ->when(fn(mixed $value) => !is_int($value))
            ->then(Check::thatInvalidFor(t::int));
    }
}
