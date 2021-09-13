<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use Closure;
use Fp\Functional\Either\Left;
use Fp\Functional\Either\Right;
use Klimick\Decode\Decoder\DecoderInterface;
use function Klimick\Decode\Decoder\decode;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

final class Check
{
    /**
     * @param DecoderInterface<mixed> $decoder
     * @return Closure(mixed): void
     */
    public static function thatValidFor(DecoderInterface $decoder): Closure
    {
        return function(mixed $value) use ($decoder): void {
            $testData = json_encode([
                'data' => $value,
                'decoder' => $decoder->name(),
            ]);

            assertInstanceOf(Right::class, decode($value, $decoder), "Should decode: {$testData}");
            assertTrue($decoder->is($value), "Should be valid: {$testData}");
        };
    }

    /**
     * @param DecoderInterface<mixed> $decoder
     * @return Closure(mixed): void
     */
    public static function thatInvalidFor(DecoderInterface $decoder): Closure
    {
        return function(mixed $value) use ($decoder): void {
            $testData = json_encode([
                'data' => $value,
                'decoder' => $decoder->name(),
            ]);

            assertInstanceOf(Left::class, decode($value, $decoder), "Should not decode: {$testData}");
            assertFalse($decoder->is($value), "Should not be valid: {$testData}");
        };
    }
}
