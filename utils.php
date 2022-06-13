<?php

declare(strict_types=1);

namespace Klimick\Decode\Utils;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\DecoderInterface;
use function Fp\Collection\every;
use function Fp\Collection\keys;
use function Fp\Collection\map;
use function Fp\Collection\tail;

/**
 * @template K of array-key
 * @template A
 * @template KOut of array-key
 * @template B
 *
 * @param iterable<K, A> $collection
 * @param callable(A): KOut $group
 * @param callable(A): B $map
 * @param callable(B, B): B $reduce
 * @return array<KOut, B>
 */
function groupMapReduce(iterable $collection, callable $group, callable $map, callable $reduce): array
{
    $grouped = [];

    foreach ($collection as $item) {
        $key = $group($item);

        if (array_key_exists($key, $grouped)) {
            $grouped[$key] = $reduce($grouped[$key], $map($item));
        } else {
            $grouped[$key] = $map($item);
        }
    }

    return $grouped;
}

/**
 * @param list<string> $path
 * @return Option<mixed>
 * @psalm-pure
 */
function getByPath(array $path, array $shape): Option
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
            return getByPath($rest, $shape[$key]);
        }
    }

    return Option::none();
}

/**
 * @return non-empty-string
 * @psalm-pure
 */
function getTypename(mixed $value): string
{
    /** @var non-empty-string */
    return match (get_debug_type($value)) {
        'null' => 'null',
        'bool' => $value ? 'true' : 'false',
        'int', 'float' => (string) $value,
        'string' => "'{$value}'",
        'array' => getArrayTypeName($value),
        default => is_object($value) ? get_class($value) : 'unknown',
    };
}

/**
 * @return non-empty-string
 * @psalm-pure
 */
function getArrayTypeName(array $arr): string
{
    $isList = every(keys($arr), fn($k) => is_int($k));

    $types = $isList
        ? map($arr, fn(mixed $v) => getTypename($v))
        : map($arr, fn(mixed $v, string|int $k) => $k . ': ' . getTypename($v));

    return 'array{' . implode(', ', $types) . '}';
}

/**
 * string() -> 'string'
 * string()->from('$.key1', '$.key2') -> 'array{key1: string} | array{key2: string}'
 *
 * @return non-empty-string
 * @psalm-pure
 */
function getAliasedTypename(DecoderInterface $decoder): string
{
    $aliases = $decoder->getAliases();
    $typename = $decoder->name();

    if (empty($aliases)) {
        return $typename;
    }

    $withoutPrefix = fn(string $alias): string => str_replace('$.', '', $alias);

    return implode(' | ', map($aliases, fn($alias) => $alias === '$'
        ? $typename
        : "array{{$withoutPrefix($alias)}: {$typename}}"));
}
