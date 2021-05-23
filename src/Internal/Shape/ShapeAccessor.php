<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Option\None;
use Fp\Functional\Option\Option;
use Fp\Functional\Option\Some;
use Klimick\Decode\Decoder;
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
    public static function access(Decoder $decoder, int|string $key, array $shape): Option
    {
        if ($decoder instanceof AliasedDecoder) {
            return self::dotAccess(explode('.', $decoder->alias), $shape);
        }

        if ($decoder instanceof FromSelfDecoder) {
            return new Some($shape);
        }

        if (array_key_exists($key, $shape)) {
            return new Some($shape[$key]);
        }

        return new None();
    }

    /**
     * @param non-empty-list<string> $path
     * @psalm-pure
     */
    private static function dotAccess(array $path, array $shape): Option
    {
        $key = $path[0];
        $rest = array_slice($path, offset: 1);

        if (empty($rest)) {
            return new Some($shape[$key] ?? null);
        }

        if (array_key_exists($key, $shape) && is_array($shape[$key])) {
            return self::dotAccess($rest, $shape[$key]);
        }

        return new None();
    }
}
