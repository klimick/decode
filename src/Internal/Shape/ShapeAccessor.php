<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Internal\ConstantDecoder;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;

/**
 * @psalm-immutable
 */
final class ShapeAccessor
{
    /**
     * @return Option<mixed>
     * @psalm-pure
     */
    public static function access(AbstractDecoder $decoder, int|string $key, array $shape): Option
    {
        return self::getConstant($decoder)
            ->orElse(fn() => self::getAliased($decoder, $shape))
            ->orElse(fn() => self::getByKey($decoder, $key, $shape));
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
    private static function getAliased(AbstractDecoder $decoder, array $shape): Option
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
    private static function getByKey(AbstractDecoder $decoder, int|string $key, array $shape): Option
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
}
