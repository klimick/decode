<?php

declare(strict_types=1);

namespace Klimick\PsalmTest;

use JetBrains\PhpStorm\NoReturn;
use RuntimeException;

final class NoCode
{
    /**
     * @psalm-suppress UndefinedAttributeClass
     * @psalm-return never
     */
    #[NoReturn]
    public static function here(): void
    {
        throw new RuntimeException('???');
    }
}
