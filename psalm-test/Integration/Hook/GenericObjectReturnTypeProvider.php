<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Hook;

use Closure;
use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Psalm;
use Klimick\PsalmTest\StaticType\StaticTypeInterface;
use Klimick\PsalmTest\StaticType\StaticTypes;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TGenericObject;
use function Fp\Collection\first;
use function Fp\Collection\second;
use function Fp\Evidence\proveTrue;

final class GenericObjectReturnTypeProvider implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [StaticTypes::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Type\Union
    {
        $return_type = Option::do(function() use ($event) {
            yield proveTrue('generic' === $event->getMethodNameLowercase());

            $type_constructor = yield self::getTypeConstructor($event);
            $type_params = yield self::getTypeParams($event);

            $inferred_type = new Type\Union([
                new Type\Atomic\TGenericObject($type_constructor, $type_params),
            ]);

            return new Type\Union([
                new Type\Atomic\TGenericObject(StaticTypeInterface::class, [$inferred_type]),
            ]);
        });

        return $return_type->get();
    }

    /**
     * @return Option<string>
     */
    private static function getTypeConstructor(MethodReturnTypeProviderEvent $event): Option
    {
        return first($event->getCallArgs())
            ->flatMap(Psalm::getArgType(from: $event))
            ->flatMap(Psalm::asSingleAtomicOf(class: Type\Atomic\TLiteralClassString::class))
            ->map(fn($atomic) => $atomic->value);
    }

    /**
     * @return Option<non-empty-list<Type\Union>>
     */
    private static function getTypeParams(MethodReturnTypeProviderEvent $event): Option
    {
        return second($event->getCallArgs())
            ->flatMap(Psalm::getArgType(from: $event))
            ->flatMap(Psalm::asSingleAtomicOf(class: Type\Atomic\TKeyedArray::class))
            ->filter(fn($keyed_array) => $keyed_array->is_list)
            ->map(fn($keyed_array) => $keyed_array->properties)
            ->flatMap(self::collectTypeParams());
    }

    /**
     * @return Closure(non-empty-array<array-key, Type\Union>): Option<non-empty-list<Type\Union>>
     */
    private static function collectTypeParams(): Closure
    {
        return function(array $properties) {
            return Option::do(function() use ($properties) {
                $type_param = [];

                foreach ($properties as $property) {
                    $type_param[] = yield Option::some($property)
                        ->flatMap(Psalm::asSingleAtomicOf(class: TGenericObject::class))
                        ->flatMap(Psalm::getTypeParam(of: StaticTypeInterface::class, position: 0));
                }

                return $type_param;
            });
        };
    }
}
