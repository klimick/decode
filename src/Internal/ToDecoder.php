<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Klimick\Decode\Decoder;

final class ToDecoder
{
    /**
     * @template T
     * @psalm-pure
     *
     * @psalm-param pure-callable(): Decoder<T>|Decoder<T> $decoder
     * @return Decoder<T>
     */
    public static function for(callable|Decoder $decoder)
    {
        return is_callable($decoder) ? $decoder() : $decoder;
    }

    /**
     * @template T
     * @psalm-pure
     *
     * @psalm-param array<array-key, pure-callable(): Decoder<T>|Decoder<T>> $decoders
     * @return array<array-key, Decoder<T>>
     */
    public static function forAll(array $decoders): array
    {
        $_decoders = [];

        foreach ($decoders as $prop => $decoderOrCallable) {
            $_decoders[$prop] = is_callable($decoderOrCallable)
                ? $decoderOrCallable()
                : $decoderOrCallable;
        }

        return $_decoders;
    }
}
