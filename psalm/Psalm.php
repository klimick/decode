<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use PhpParser\Node;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Type;
use Fp\Functional\Option\Option;
use Psalm\NodeTypeProvider;
use function Fp\Cast\asList;
use function Fp\Evidence\proveOf;
use function Fp\Evidence\proveTrue;

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

    /**
     * @return Option<string>
     */
    public static function getArgName(Node\Arg $arg): Option
    {
        return proveOf($arg->name, Node\Identifier::class)->map(fn($id) => $id->name);
    }
}
