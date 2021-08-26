<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use Closure;
use Fp\Functional\Either\Left;
use Fp\Functional\Either\Right;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\decode;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

final class Check
{
    /**
     * @param AbstractDecoder<mixed> $decoder
     * @return Closure(mixed): void
     */
    public static function thatValidFor(AbstractDecoder $decoder): Closure
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
     * @param AbstractDecoder<mixed> | callable(): AbstractDecoder<mixed> $decoderOrCallable
     * @return Closure(mixed): void
     */
    public static function thatInvalidFor(callable|AbstractDecoder $decoderOrCallable): Closure
    {
        $decoder = is_callable($decoderOrCallable)
            ? $decoderOrCallable()
            : $decoderOrCallable;

        return function(mixed $value) use ($decoder): void {
            assertInstanceOf(Left::class, decode($value, $decoder));
        };
    }
}
