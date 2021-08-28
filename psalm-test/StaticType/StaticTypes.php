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
     * @template K of array-key
     * @template V
     *
     * @param StaticTypeInterface<K> $k
     * @param StaticTypeInterface<V> $v
     * @return StaticTypeInterface<array<K, V>>
     */
    public static function array(StaticTypeInterface $k, StaticTypeInterface $v): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @template K of array-key
     * @template V
     *
     * @param StaticTypeInterface<K> $k
     * @param StaticTypeInterface<V> $v
     * @return StaticTypeInterface<non-empty-array<K, V>>
     */
    public static function nonEmptyArray(StaticTypeInterface $k, StaticTypeInterface $v): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @template V
     *
     * @param StaticTypeInterface<V> $v
     * @return StaticTypeInterface<list<V>>
     */
    public static function list(StaticTypeInterface $v): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @template V
     *
     * @param StaticTypeInterface<V> $v
     * @return StaticTypeInterface<non-empty-list<V>>
     */
    public static function nonEmptyList(StaticTypeInterface $v): StaticTypeInterface
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
     * @return StaticTypeInterface<positive-int>
     */
    public static function positiveInt(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<float>
     */
    public static function float(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<bool>
     */
    public static function bool(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<array-key>
     */
    public static function arrayKey(): StaticTypeInterface
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
     * @param class-string $ofType
     * @param non-empty-list<StaticTypeInterface> $withParams
     */
    public static function generic(string $ofType, array $withParams): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @template T of object
     *
     * @param non-empty-list<StaticTypeInterface<T>> $types
     * @return  StaticTypeInterface<T>
     */
    public static function intersection(array $types): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @template T
     * @no-named-arguments
     *
     * @param non-empty-list<StaticTypeInterface<T>> ...$types
     * @return StaticTypeInterface<T>
     */
    public static function union(array $types): StaticTypeInterface
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

    /**
     * @return StaticTypeInterface<numeric>
     */
    public static function numeric(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<numeric-string>
     */
    public static function numericString(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<mixed>
     */
    public static function mixed(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<scalar>
     */
    public static function scalar(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<null>
     */
    public static function null(): StaticTypeInterface
    {
        NoCode::here();
    }

    /**
     * @return StaticTypeInterface<non-empty-string>
     */
    public static function nonEmptyString(): StaticTypeInterface
    {
        NoCode::here();
    }
}
