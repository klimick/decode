<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration;

use Closure;
use PhpParser\Node;
use Psalm\Type;
use Fp\Functional\Option\Option;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use function Fp\Cast\asList;
use function Fp\Collection\at;
use function Fp\Collection\first;
use function Fp\Evidence\proveOf;

final class Psalm
{
    /**
     * @return Closure(Node\Expr | Node\Name | Node\Stmt\Return_): Option<Type\Union>
     */
    public static function getType(MethodReturnTypeProviderEvent $from): Closure
    {
        return fn(Node\Expr | Node\Name | Node\Stmt\Return_ $for) => Option::fromNullable(
            $from->getSource()
                ->getNodeTypeProvider()
                ->getType($for)
        );
    }

    /**
     * @return Closure(Node\Arg): Option<Type\Union>
     */
    public static function getArgType(MethodReturnTypeProviderEvent $from): Closure
    {
        $getType = self::getType($from);

        return fn(Node\Arg $arg) => $getType($arg->value);
    }

    /**
     * @return Closure(Type\Union): Option<Type\Atomic>
     */
    public static function asSingleAtomic(): Closure
    {
        return fn(Type\Union $union) => Option::some($union)
            ->map(fn($union) => $union->getAtomicTypes())
            ->map(fn($atomics) => asList($atomics))
            ->filter(fn($atomics) => 1 === count($atomics))
            ->flatMap(fn($atomics) => first($atomics));
    }

    /**
     * @param class-string $of
     * @param 0|positive-int $position
     * @return Closure(Type\Atomic\TGenericObject): Option<Type\Union>
     */
    public static function getTypeParam(string $of, int $position): Closure
    {
        return fn(Type\Atomic\TGenericObject $generic) => Option::some($generic)
            ->filter(fn($a) => $a->value === $of)
            ->flatMap(fn($a) => at($a->type_params, $position));
    }

    /**
     * @template TAtomic of Type\Atomic
     *
     * @param class-string<TAtomic> $class
     * @return Closure(Type\Union): Option<TAtomic>
     */
    public static function asSingleAtomicOf(string $class): Closure
    {
        $asSingleAtomic = self::asSingleAtomic();

        return fn(Type\Union $union) => $asSingleAtomic($union)
            ->flatMap(fn(Type\Atomic $atomic) => proveOf($atomic, $class));
    }
}
