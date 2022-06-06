<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Error\DecodeErrorInterface;
use Klimick\Decode\Decoder\Error\UndefinedError;
use Klimick\Decode\Decoder\HighOrder\HighOrderDecoder;
use function Fp\Collection\at;
use function Fp\Collection\tail;
use function Fp\Evidence\proveOf;

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
        array $shape,
    ): Either {
        return self::getConstant($decoder)
            ->orElse(fn() => self::getByAliasedKey($decoder, $shape))
            ->orElse(fn() => self::getByOriginalKey($key, $shape))
            ->orElse(fn() => self::getDefault($decoder))
            ->toRight(fn() => self::undefined($context, $decoder, $key))
            ->flatMap(fn($value) => self::decode($decoder, $value, $key, $context));
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getConstant(DecoderInterface $decoder): Option
    {
        return proveOf($decoder, ConstantDecoder::class)
            ->map(fn($decoder): mixed => $decoder->constant);
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getByAliasedKey(DecoderInterface $decoder, array $shape): Option
    {
        return proveOf($decoder, HighOrderDecoder::class)
            ->flatMap(fn($decoder) => Option::fromNullable($decoder->asFrom()))
            ->flatMap(fn($decoder) => '$' !== $decoder->alias
                ? self::dotAccess(tail(explode('.', $decoder->alias)), $shape)
                : Option::some($shape));
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getByOriginalKey(int|string $key, array $shape): Option
    {
        return at($shape, $key);
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getDefault(DecoderInterface $decoder): Option
    {
        return proveOf($decoder, HighOrderDecoder::class)
            ->flatMap(fn($decoder) => Option::fromNullable($decoder->asDefault()))
            ->map(fn($decoder): mixed => $decoder->default);
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

    /**
     * @return non-empty-list<DecodeErrorInterface>
     * @psalm-pure
     */
    private static function undefined(Context $context, DecoderInterface $decoder, int|string $key): array
    {
        return [
            new UndefinedError($context(
                name: $decoder->name(),
                actual: null,
                key: (string)$key,
            )),
        ];
    }

    /**
     * @template TDecoded
     *
     * @param DecoderInterface<TDecoded> $decoder
     * @return Either<non-empty-list<DecodeErrorInterface>, TDecoded>
     * @psalm-pure
     */
    private static function decode(DecoderInterface $decoder, mixed $value, int|string $key, Context $context): Either
    {
        return $decoder->decode($value, $context(
            name: $decoder->name(),
            actual: $value,
            key: (string)$key,
        ));
    }
}
