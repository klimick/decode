<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use PhpParser\Node;
use Psalm\Type;
use Fp\Functional\Option\Option;
use Psalm\NodeTypeProvider;

final class Psalm
{
    /**
     * @return Option<Type\Union>
     */
    public static function getType(
        NodeTypeProvider $type_provider,
        Node\Expr|Node\Name|Node\Stmt\Return_ $node,
    ): Option
    {
        return Option::fromNullable($type_provider->getType($node));
    }
}
