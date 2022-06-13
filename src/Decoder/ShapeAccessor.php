<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Fp\Streams\Stream;
use Klimick\Decode\Error\Context;
use Klimick\Decode\Error\DecodeError;
use function array_key_exists;
use function explode;
use function Fp\Collection\at;
use function Fp\Collection\tail;
use function is_array;

/**
 * @psalm-immutable
 */
final class ShapeAccessor
{
    /**
     * @template TDecoded
     *
     * @param Context<DecoderInterface> $context
     * @param DecoderInterface<TDecoded> $decoder
     * @return Either<non-empty-list<DecodeError>, TDecoded>
     *
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
     * @param Context<DecoderInterface> $context
     * @return Either<non-empty-list<DecodeError>, never>
     *
     * @psalm-pure
     */
    private static function undefinedError(Context $context, DecoderInterface $decoder, int|string $key): Either
    {
        return Either::left([
            DecodeError::undefinedError(
                context: $context($decoder, actual: null, key: $key),
                aliases: $decoder->getAliases(),
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
