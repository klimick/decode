<?php

declare(strict_types=1);

namespace Klimick\Decode\Utils;

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
