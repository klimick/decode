<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Fp\Functional\Either\Right;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\decode;
use function Klimick\Decode\fallback;
use function Klimick\Decode\Test\Helper\forAll;

final class FallbackDecoderTest extends TestCase
{
    public function testValidForAllGivenFallbacks(): void
    {
        forAll(Gen::mixed())
            ->then(function(mixed $value) {
                $decoder = fallback($value);
                self::assertInstanceOf(Right::class, decode($value, $decoder));
            });
    }
}
