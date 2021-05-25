<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use Closure;
use Fp\Functional\Either\Left;
use Fp\Functional\Either\Right;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\decode;
use function PHPUnit\Framework\assertInstanceOf;

final class Check
{
    /**
     * @param AbstractDecoder<mixed> | callable(): AbstractDecoder<mixed> $decoderOrCallable
     * @return Closure(mixed): void
     */
    public static function thatValidFor(callable|AbstractDecoder $decoderOrCallable): Closure
    {
        $decoder = is_callable($decoderOrCallable)
            ? $decoderOrCallable()
            : $decoderOrCallable;

        return function(mixed $value) use ($decoder): void {
            $result = decode($value, $decoder);
            assertInstanceOf(Right::class, $result, json_encode($value));
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
