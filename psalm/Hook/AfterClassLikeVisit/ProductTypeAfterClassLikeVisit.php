<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Hook\AfterClassLikeVisit;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\ProductType;
use Klimick\PsalmDecode\Helper\StorageManager;
use Klimick\PsalmDecode\Helper\TypedArg;
use Klimick\PsalmDecode\Helper\TypedArgGrabber;
use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\Storage\ClassLikeStorage;

final class ProductTypeAfterClassLikeVisit implements AfterClassLikeVisitInterface
{
    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        Option::do(function() use ($event) {
            $typed_args = yield TypedArgGrabber::grab(
                fromEvent: $event,
                forSubClassOf: ProductType::class,
                forMetaFunction: 'Klimick\Decode\Decoder\shape',
            );

            $storage = $event->getStorage();

            StorageManager::addMethod(
                named_as: '__construct',
                to: $storage,
                with_params: $typed_args
                    ->tap(fn($arg) => self::addPseudoProperty($storage, $arg))
                    ->map(fn($arg) => $arg->toParameterLike())
                    ->toArray(),
            );

            StorageManager::makeImmutable($storage);
        });
    }

    private static function addPseudoProperty(ClassLikeStorage $storage, TypedArg $arg): void
    {
        $storage->pseudo_property_get_types['$' . $arg->name] = $arg->type;
    }
}
