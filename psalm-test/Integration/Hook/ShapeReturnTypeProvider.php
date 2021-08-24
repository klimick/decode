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
                ->flatMap(fn($arg) => Psalm::getType($arg->value, $event))
                ->flatMap(fn($arg_type) => Psalm::asSingleAtomic($arg_type))
                ->filter(fn($atomic) => $atomic instanceof Type\Atomic\TKeyedArray);

            $remapped = [];

            foreach ($arg_type->properties as $key => $type) {
                $remapped[$key] = yield Psalm::asSingleAtomic($type)
                    ->filter(fn($atomic) => $atomic instanceof Type\Atomic\TGenericObject)
                    ->filter(fn($atomic) => $atomic->value === StaticTypeInterface::class)
                    ->flatMap(fn($atomic) => first($atomic->type_params));
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
