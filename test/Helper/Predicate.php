<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

final class Predicate
{
    /**
     * @psalm-assert-if-true list $value
     */
    public static function isList(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if (empty($value)) {
            return true;
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    /**
     * @psalm-assert-if-true non-empty-list $value
     */
    public static function isNonEmptyList(mixed $value): bool
    {
        return self::isList($value) && !empty($value);
    }

    /**
     * @psalm-assert-if-true non-empty-array $value
     */
    public static function isNonEmptyArray(mixed $value): bool
    {
        return is_array($value) && !empty($value);
    }

    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    public static function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && '' !== $value;
    }
}
