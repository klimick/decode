<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Helper;

use DateTimeImmutable;
use Fp\Functional\Option\Option;

final class AssertType
{
    /**
     * @param Option<bool> $_param
     */
    public static function bool(Option $_param): void
    {
    }

    /**
     * @param Option<array-key> $_param
     */
    public static function arrKey(Option $_param): void
    {
    }

    /**
     * @param Option<DateTimeImmutable> $_param
     */
    public static function datetime(Option $_param): void
    {
    }

    /**
     * @param Option<scalar> $_param
     */
    public static function scalar(Option $_param): void
    {
    }

    /**
     * @param Option<float> $_param
     */
    public static function float(Option $_param): void
    {
    }

    /**
     * @param Option<int> $_param
     */
    public static function int(Option $_param): void
    {
    }

    /**
     * @param Option<positive-int> $_param
     */
    public static function positiveInt(Option $_param): void
    {
    }

    /**
     * @param Option<mixed> $_param
     */
    public static function mixed(Option $_param): void
    {
    }

    /**
     * @param Option<null> $_param
     */
    public static function null(Option $_param): void
    {
    }

    /**
     * @param Option<string> $_param
     */
    public static function string(Option $_param): void
    {
    }

    /**
     * @param Option<non-empty-string> $_param
     */
    public static function nonEmptyString(Option $_param): void
    {
    }

    /**
     * @param Option<numeric> $_param
     */
    public static function numeric(Option $_param): void
    {
    }
}
