<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use Closure;
use Fp\Functional\Either\Left;
use Fp\Functional\Either\Right;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\decode;
use function PHPUnit\Framework\assertInstanceOf;

final class Check
{
    /**
     * @param DecoderInterface<mixed> | callable(): DecoderInterface<mixed> $decoderOrCallable
     * @return Closure(mixed): void
     */
    public static function thatValidFor(callable|DecoderInterface $decoderOrCallable): Closure
    {
        $decoder = is_callable($decoderOrCallable)
            ? $decoderOrCallable()
            : $decoderOrCallable;

        return function(mixed $value) use ($decoder): void {
            $result = decode($decoder, $value);
            assertInstanceOf(Right::class, $result, json_encode($value));
        };
    }

    /**
     * @param DecoderInterface<mixed> | callable(): DecoderInterface<mixed> $decoderOrCallable
     * @return Closure(mixed): void
     */
    public static function thatInvalidFor(callable|DecoderInterface $decoderOrCallable): Closure
    {
        $decoder = is_callable($decoderOrCallable)
            ? $decoderOrCallable()
            : $decoderOrCallable;

        return function(mixed $value) use ($decoder): void {
            assertInstanceOf(Left::class, decode($decoder, $value));
        };
    }
}
