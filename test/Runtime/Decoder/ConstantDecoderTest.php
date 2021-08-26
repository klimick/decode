<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Either\Right;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\constant;
use function Klimick\Decode\Test\Helper\forAll;

final class ConstantDecoderTest extends TestCase
{
    public function testValidForAllGivenConstants(): void
    {
        forAll(Gen::mixed())
            ->then(function(mixed $value) {
                $decoder = constant($value);
                self::assertInstanceOf(Right::class, decode($value, $decoder));
            });
    }
}
