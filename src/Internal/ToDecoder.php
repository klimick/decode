<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Klimick\Decode\DecoderInterface;

final class ToDecoder
{
    /**
     * @template T
     * @psalm-pure
     *
     * @psalm-param pure-callable(): DecoderInterface<T>|DecoderInterface<T> $decoder
     * @return DecoderInterface<T>
     */
    public static function for(callable|DecoderInterface $decoder)
    {
        return is_callable($decoder) ? $decoder() : $decoder;
    }

    /**
     * @template T
     * @psalm-pure
     *
     * @psalm-param array<array-key, pure-callable(): DecoderInterface<T>|DecoderInterface<T>> $decoders
     * @return array<array-key, DecoderInterface<T>>
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
