<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Internal\ConstantDecoder;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use function Klimick\Decode\Decoder\valid;

/**
 * @psalm-immutable
 * @psalm-import-type ValidShapeProperties from ShapePropertySemigroup
 */
final class ShapeAccessor
{
    /**
     * @return Either<Invalid, ValidShapeProperties>
     * @psalm-pure
     */
    public static function decodeProperty(
        Context $context,
        AbstractDecoder $decoder,
        string $key,
        array $shape,
        bool $partial = false,
    ): Either
    {
        return self::getConstant($decoder)
            ->orElse(fn() => self::getByAliasedKey($decoder, $shape))
            ->orElse(fn() => self::getByOriginalKey($decoder, $key, $shape))
            ->map(fn($value) => [$key => $value])
            ->orElse(fn() => self::asEmptyWhenOptional($decoder, $partial))
            ->toRight(fn() => self::undefinedProperty($context, $decoder, $key))
            ->flatMap(fn($value) => self::decode($decoder, $value, $key, $context));
    }

    /**
     * @return Option<array>
     */
    private static function asEmptyWhenOptional(AbstractDecoder $decoder, bool $partial): Option
    {
        return $partial || ($decoder instanceof HighOrderDecoder && $decoder->isOptional())
            ? Option::some([])
            : Option::none();
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getConstant(AbstractDecoder $decoder): Option
    {
        return $decoder instanceof ConstantDecoder
            ? Option::some($decoder->constant)
            : Option::none();
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getByAliasedKey(AbstractDecoder $decoder, array $shape): Option
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
    private static function getByOriginalKey(AbstractDecoder $decoder, string $key, array $shape): Option
    {
        return array_key_exists($key, $shape)
            ? Option::some($shape[$key])
            : self::getDefault($decoder);
    }

    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    private static function getDefault(AbstractDecoder $decoder): Option
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
    private static function undefinedProperty(Context $context, AbstractDecoder $decoder, string $property): Invalid
    {
        return new Invalid([
            new UndefinedError($context(
                name: $decoder->name(),
                actual: null,
                key: $property,
            )),
        ]);
    }

    /**
     * @return Either<Invalid, ValidShapeProperties>
     * @psalm-pure
     */
    private static function decode(AbstractDecoder $decoder, array $value, string $key, Context $context): Either
    {
        if (empty($value)) {
            return valid([]);
        }

        return $decoder->decode($value[$key], $context($decoder->name(), $value[$key], $key))
            ->map(fn(Valid $valid) => new Valid([$key => $valid->value]));
    }
}
