<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Internal\FallbackDecoder;
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
        if ($decoder instanceof HighOrderDecoder) {
            if ($decoder->isDefault() && !array_key_exists($key, $shape)) {
                return Option::some($decoder->asDefault()->default);
            }

            if ($decoder->isFrom()) {
                $path = explode('.', preg_replace('/^\$\.(.+)/', '$1', $decoder->asFrom()->alias));

                return $path !== ['$']
                    ? self::dotAccess($path, $shape)
                    : Option::some($shape);
            }
        }

        if ($decoder instanceof FallbackDecoder) {
            return Option::some($decoder->fallback);
        }

        if (array_key_exists($key, $shape)) {
            return Option::some($shape[$key]);
        }

        return Option::none();
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
