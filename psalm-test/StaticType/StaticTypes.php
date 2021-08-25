<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\StaticType;

use Klimick\PsalmTest\NoCode;

final class StaticTypes
{
    public static function shape(array $types): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @psalm-return StaticTypeInterface<string>
     */
    public static function string(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<int>
     */
    public static function int(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     * @return StaticTypeInterface<T>
     */
    public static function object(string $class): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @template T
     * @no-named-arguments
     *
     * @param StaticTypeInterface<T> ...$types
     * @return StaticTypeInterface<T>
     */
    public static function union(StaticTypeInterface ...$types): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @template T of scalar
     *
     * @param T $literal
     * @return StaticTypeInterface<T>
     */
    public static function literal(mixed $literal): StaticTypeInterface
    {
        NoCode::here();
    }
}
