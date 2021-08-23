<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\StaticType;

final class StaticTypes
{
    /**
     * @psalm-return StaticTypeInterface<string> & StringStaticType
     */
    public static function string(): StaticTypeInterface
    {
        return new StringStaticType();
    }

    /**
     * @return StaticTypeInterface<int> & IntStaticType
     */
    public static function int(): StaticTypeInterface
    {
        return new IntStaticType();
    }

    /**
     * @template T of scalar
     *
     * @param T $literal
     * @return StaticTypeInterface<T> & StaticLiteralType<T>
     */
    public static function literal(mixed $literal): StaticTypeInterface
    {
        return new StaticLiteralType($literal);
    }
}
