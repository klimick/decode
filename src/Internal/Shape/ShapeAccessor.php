<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Internal\HighOrder\AliasedDecoder;
use Klimick\Decode\Internal\HighOrder\FromSelfDecoder;

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
        return match (true) {
            $decoder instanceof AliasedDecoder => self::dotAccess(explode('.', $decoder->alias), $shape),
            $decoder instanceof FromSelfDecoder => Option::some($shape),
            array_key_exists($key, $shape) => Option::some($shape[$key]),
            default => Option::none(),
        };
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
