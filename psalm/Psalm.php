<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use PhpParser\Node;
use Psalm\Plugin\EventHandler\Event\AfterExpressionAnalysisEvent;
use Psalm\Type;
use Fp\Functional\Option\Option;
use Psalm\NodeTypeProvider;
use function Fp\Cast\asList;
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
     * @return Option<Type\Atomic>
     */
    public static function asSingleAtomic(Type\Union $union): Option
    {
        return Option::do(function() use ($union) {
            $atomics = asList($union->getAtomicTypes());
            yield proveTrue(1 === count($atomics));

            return $atomics[0];
        });
    }

    public static function classExtends(string $class, string $from, AfterExpressionAnalysisEvent $event): bool
    {
        $isSubclass = fn(): bool => $event
            ->getCodebase()->classlikes
            ->classExtends($class, $from);

        return Option::try($isSubclass)->getOrElse(false);
    }
}
