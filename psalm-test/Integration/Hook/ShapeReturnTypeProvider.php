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
use function Fp\Collection\first;
use function Fp\Evidence\proveTrue;

final class ShapeReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [StaticTypes::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Type\Union
    {
        $return_type = Option::do(function() use ($event) {
            yield proveTrue('shape' === $event->getMethodNameLowercase());

            $arg_type = yield first($event->getCallArgs())
                ->flatMap(Psalm::getArgType(from: $event))
                ->flatMap(Psalm::asSingleAtomicOf(class: Type\Atomic\TKeyedArray::class));

            $remapped = [];

            foreach ($arg_type->properties as $key => $type) {
                $remapped[$key] = yield Option::some($type)
                    ->flatMap(Psalm::asSingleAtomicOf(class: Type\Atomic\TGenericObject::class))
                    ->flatMap(Psalm::getTypeParam(of: StaticTypeInterface::class, position: 0));
            }

            $shape = new Type\Union([
                new Type\Atomic\TKeyedArray($remapped),
            ]);

            return new Type\Union([
                new Type\Atomic\TGenericObject(StaticTypeInterface::class, [$shape]),
            ]);
        });

        return $return_type->get();
    }
}
