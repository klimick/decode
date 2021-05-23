<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use Closure;
use Fp\Functional\Either\Left;
use Fp\Functional\Either\Right;
use Klimick\Decode\Decoder;
use function Klimick\Decode\decode;
use function PHPUnit\Framework\assertInstanceOf;

final class Check
{
    /**
     * @param Decoder<mixed> | callable(): Decoder<mixed> $decoderOrCallable
     * @return Closure(mixed): void
     */
    public static function thatValidFor(callable|Decoder $decoderOrCallable): Closure
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
     * @param Decoder<mixed> | callable(): Decoder<mixed> $decoderOrCallable
     * @return Closure(mixed): void
     */
    public static function thatInvalidFor(callable|Decoder $decoderOrCallable): Closure
    {
        $decoder = is_callable($decoderOrCallable)
            ? $decoderOrCallable()
            : $decoderOrCallable;

        return function(mixed $value) use ($decoder): void {
            assertInstanceOf(Left::class, decode($value, $decoder));
        };
    }
}
