<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Fp\Streams\Stream;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Error\DecodeErrorInterface;
use Klimick\Decode\Decoder\Error\UndefinedError;
use function explode;
use function is_array;
use function array_key_exists;
use function Fp\Collection\at;
use function Fp\Collection\tail;

/**
 * @psalm-immutable
 */
final class ShapeAccessor
{
    /**
     * @template TDecoded
     *
     * @param DecoderInterface<TDecoded> $decoder
     * @return Either<non-empty-list<DecodeErrorInterface>, TDecoded>
     * @psalm-pure
     */
    public static function decodeProperty(
        Context $context,
        DecoderInterface $decoder,
        int|string $key,
        mixed $shape,
    ): Either {
        if ($decoder instanceof ConstantlyDecoder) {
            return Either::right($decoder->constant);
        }

        if (!is_array($shape)) {
            return self::undefinedError($context, $decoder, $key);
        }

        return Stream::emits($decoder->getAliases())
            ->filterMap(fn($alias) => '$' !== $alias
                ? self::dotAccess(tail(explode('.', $alias)), $shape)
                : Option::some($shape))
            ->firstElement()
            ->orElse(fn() => at($shape, $key))
            ->fold(
                ifSome: fn($v) => $decoder->decode($v, $context($decoder, actual: $v, key: (string) $key)),
                ifNone: fn() => $decoder->getDefault()->fold(
                    ifSome: fn($default) => Either::right($default),
                    ifNone: function() use ($decoder, $context, $key) {
                        if ($decoder instanceof OptionDecoder) {
                            /** @var Either<empty, TDecoded> */
                            return Either::right(Option::none());
                        }

                        return self::undefinedError($context, $decoder, $key);
                    },
                ),
            );
    }

    /**
     * @return Either<non-empty-list<DecodeErrorInterface>, never>
     * @psalm-pure
     */
    private static function undefinedError(Context $context, DecoderInterface $decoder, int|string $key): Either
    {
        return Either::left([
            new UndefinedError(
                $context($decoder, actual: null, key: $key),
                $decoder->getAliases(),
            ),
        ]);
    }

    /**
     * @param list<string> $path
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function dotAccess(array $path, array $shape): Option
    {
        if (empty($path)) {
            return Option::none();
        }

        $key = $path[0];
        $rest = tail($path);

        if (array_key_exists($key, $shape)) {
            if (empty($rest)) {
                return Option::some($shape[$key]);
            }

            if (is_array($shape[$key])) {
                return self::dotAccess($rest, $shape[$key]);
            }
        }

        return Option::none();
    }
}
