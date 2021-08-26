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
use function Fp\Collection\first;
use function Fp\Collection\tail;
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
            $type_params = yield self::getTypeParams($event)->filter(fn($params) => !empty($params));

            $inferred_type = new Type\Union([
                new Type\Atomic\TGenericObject($type_constructor, $type_params),
            ]);

            return new Type\Union([
                new Type\Atomic\TGenericObject(StaticTypeInterface::class, [$inferred_type])
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
            ->flatMap(fn($arg) => Psalm::getType($arg->value, $event))
            ->flatMap(fn($arg_type) => Psalm::asSingleAtomic($arg_type))
            ->filter(fn($atomic) => $atomic instanceof Type\Atomic\TLiteralClassString)
            ->map(fn($atomic) => $atomic->value);
    }

    /**
     * @return Option<list<Type\Union>>
     */
    private static function getTypeParams(MethodReturnTypeProviderEvent $event): Option
    {
        return Option::do(function() use ($event) {
            $type_params = [];

            foreach (tail($event->getCallArgs()) as $arg) {
                $type_params[] = yield Psalm::getType($arg->value, $event)
                    ->flatMap(fn($arg_type) => Psalm::asSingleAtomic($arg_type))
                    ->filter(fn($atomic) => $atomic instanceof TGenericObject)
                    ->filter(fn($atomic) => StaticTypeInterface::class === $atomic->value)
                    ->flatMap(fn($atomic) => first($atomic->type_params));
            }

            return $type_params;
        });
    }
}
