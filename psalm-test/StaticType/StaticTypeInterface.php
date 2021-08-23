<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\StaticType;

use Psalm\Type;

/**
 * @template T
 */
interface StaticTypeInterface
{
    public function toPsalm(): Type\Union;
}
