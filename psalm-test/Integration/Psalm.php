<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration;

use PhpParser\Node;
use Psalm\Type;
use Fp\Functional\Option\Option;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use function Fp\Cast\asList;
use function Fp\Collection\first;

final class Psalm
{
    /**
     * @return Option<Type\Union>
     */
    public static function getType(Node\Expr|Node\Name|Node\Stmt\Return_ $for, MethodReturnTypeProviderEvent $from): Option
    {
        return Option::fromNullable(
            $from->getSource()
                ->getNodeTypeProvider()
                ->getType($for)
        );
    }

    /**
     * @return Option<Type\Atomic>
     */
    public static function asSingleAtomic(Type\Union $union): Option
    {
        return Option::some($union)
            ->map(fn($union) => $union->getAtomicTypes())
            ->map(fn($atomics) => asList($atomics))
            ->filter(fn($atomics) => 1 === count($atomics))
            ->flatMap(fn($atomics) => first($atomics));
    }

}
