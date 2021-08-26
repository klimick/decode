<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Hook;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Psalm;
use Klimick\PsalmTest\StaticType\StaticTypeInterface;
use Klimick\PsalmTest\StaticType\StaticTypes;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TGenericObject;
use function Fp\Cast\asList;
use function Fp\Collection\first;
use function Fp\Collection\tail;
use function Fp\Evidence\proveTrue;

final class IntersectionReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [StaticTypes::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Type\Union
    {
        $return_type = Option::do(function() use ($event) {
            yield proveTrue('intersection' === $event->getMethodNameLowercase());

            [$type, $types] = yield self::getTypes($event);

            $intersected = clone $type;

            foreach ($types as $addToIntersection) {
                $intersected->addIntersectionType($addToIntersection);
            }

            return new Type\Union([
                new TGenericObject(StaticTypeInterface::class, [
                    new Type\Union([$intersected])
                ]),
            ]);
        });

        return $return_type->get();
    }

    /**
     * @return Option<array{Type\Atomic\TNamedObject, non-empty-list<Type\Atomic\TNamedObject>}>
     */
    private static function getTypes(MethodReturnTypeProviderEvent $event): Option
    {
        return Option::do(function() use ($event) {
            $types = [];

            foreach ($event->getCallArgs() as $arg) {
                $types[] = yield Psalm::getType($arg->value, $event)
                    ->flatMap(fn($arg_type) => Psalm::asSingleAtomic($arg_type))
                    ->filter(fn($atomic) => $atomic instanceof TGenericObject)
                    ->filter(fn($atomic) => StaticTypeInterface::class === $atomic->value)
                    ->flatMap(fn($atomic) => first($atomic->type_params))
                    ->flatMap(fn($atomic) => Psalm::asSingleAtomic($atomic))
                    ->filter(fn($atomic) => $atomic instanceof Type\Atomic\TNamedObject);
            }

            return [
                yield first($types),
                yield Option::some(tail($types))
                    ->filter(fn($types) => count($types) >= 1)
                    ->map(fn($types) => asList($types)),
            ];
        });
    }

}
