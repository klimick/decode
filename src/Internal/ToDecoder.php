<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Klimick\Decode\AbstractDecoder;

final class ToDecoder
{
    /**
     * @template T
     * @psalm-pure
     *
     * @psalm-param pure-callable(): AbstractDecoder<T>|AbstractDecoder<T> $decoder
     * @return AbstractDecoder<T>
     */
    public static function for(callable|AbstractDecoder $decoder)
    {
        return is_callable($decoder) ? $decoder() : $decoder;
    }

    /**
     * @template T
     * @psalm-pure
     *
     * @psalm-param array<array-key, pure-callable(): AbstractDecoder<T>|AbstractDecoder<T>> $decoders
     * @return array<array-key, AbstractDecoder<T>>
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
