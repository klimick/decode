<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\StaticType;

use Psalm\Type;

/**
 * @implements StaticTypeInterface<int>
 */
final class IntStaticType implements StaticTypeInterface
{
    public function toPsalm(): Type\Union
    {
        return Type::getInt();
    }
}
