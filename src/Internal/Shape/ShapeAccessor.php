<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Internal\ConstantDecoder;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;

/**
 * @psalm-immutable
 */
final class ShapeAccessor
{
    /**
     * @return Either<Invalid, Valid>
     * @psalm-pure
     */
    public static function decodeProperty(
        Context $context,
        DecoderInterface $decoder,
        int|string $key,
        array $shape,
    ): Either
    {
        return self::getConstant($decoder)
            ->orElse(fn() => self::getByAliasedKey($decoder, $shape))
            ->orElse(fn() => self::getByOriginalKey($decoder, $key, $shape))
            ->toRight(fn() => self::undefined($context, $decoder, $key))
            ->flatMap(fn($value) => self::decode($decoder, $value, $key, $context));
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getConstant(DecoderInterface $decoder): Option
    {
        return $decoder instanceof ConstantDecoder
            ? Option::some($decoder->constant)
            : Option::none();
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getByAliasedKey(DecoderInterface $decoder, array $shape): Option
    {
        if (!($decoder instanceof HighOrderDecoder) || !$decoder->isFrom()) {
            return Option::none();
        }

        $path = explode('.', preg_replace('/^\$\.(.+)/', '$1', $decoder->asFrom()->alias));

        return $path !== ['$']
            ? self::dotAccess($path, $shape)->orElse(fn() => self::getDefault($decoder))
            : Option::some($shape);
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getByOriginalKey(DecoderInterface $decoder, int|string $key, array $shape): Option
    {
        return array_key_exists($key, $shape)
            ? Option::some($shape[$key])
            : self::getDefault($decoder);
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getDefault(DecoderInterface $decoder): Option
    {
        return $decoder instanceof HighOrderDecoder && $decoder->isDefault()
            ? Option::some($decoder->asDefault()->default)
            : Option::none();
    }

    /**
     * @param non-empty-list<string> $path
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function dotAccess(array $path, array $shape): Option
    {
        $key = $path[0];
        $rest = array_slice($path, offset: 1);

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
     * @psalm-pure
     */
    private static function undefined(Context $context, DecoderInterface $decoder, int|string $key): Invalid
    {
        return new Invalid([
            new UndefinedError($context(
                name: $decoder->name(),
                actual: null,
                key: (string)$key,
            )),
        ]);
    }

    /**
     * @return Either<Invalid, Valid>
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
