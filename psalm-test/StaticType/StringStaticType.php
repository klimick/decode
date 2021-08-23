<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\StaticType;

use Psalm\Type;

/**
 * @implements StaticTypeInterface<string>
 */
final class StringStaticType implements StaticTypeInterface
{
    public function toPsalm(): Type\Union
    {
        return Type::getString();
    }
}
