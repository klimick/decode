<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Klimick\Decode\DecoderInterface;
use Klimick\Decode\Internal\HighOrder\AliasedDecoder;
use Klimick\Decode\Internal\HighOrder\FromSelfDecoder;

/**
 * @psalm-immutable
 */
final class ShapeAccessor
{
    /**
     * @psalm-pure
     */
    public static function access(DecoderInterface $decoder, int|string $key, array $shape): mixed
    {
        if ($decoder instanceof AliasedDecoder) {
            return self::dotAccess(explode('.', $decoder->alias), $shape);
        }

        if ($decoder instanceof FromSelfDecoder) {
            return $shape;
        }

        if (array_key_exists($key, $shape)) {
            return $shape[$key];
        }

        return UndefinedProperty::instance();
    }

    /**
     * @param non-empty-list<string> $path
     * @psalm-pure
     */
    private static function dotAccess(array $path, array $shape): mixed
    {
        $key = $path[0];
        $rest = array_slice($path, offset: 1);

        if (empty($rest)) {
            return $shape[$key] ?? UndefinedProperty::instance();
        }

        if (array_key_exists($key, $shape) && is_array($shape[$key])) {
            return self::dotAccess($rest, $shape[$key]);
        }

        return UndefinedProperty::instance();
    }
}
