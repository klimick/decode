<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Visit;

use Klimick\Decode\Decoder\SumType;
use Fp\Functional\Option\Option;
use Psalm\Plugin\EventHandler\AfterClassLikeVisitInterface;
use Psalm\Plugin\EventHandler\Event\AfterClassLikeVisitEvent;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\FunctionLikeParameter;
use Psalm\Type;

final class VisitSumType implements AfterClassLikeVisitInterface
{
    private static function matchTemplateId(ClassLikeStorage $storage): string
    {
        return 'fn-' . strtolower($storage->name) . '::' . 'match';
    }

    private static function matchReturnType(ClassLikeStorage $storage): Type\Union
    {
        return new Type\Union([
            new Type\Atomic\TTemplateParam(
                param_name: 'TMatch',
                extends: Type::getMixed(),
                defining_class: self::matchTemplateId($storage),
            ),
        ]);
    }

    public static function afterClassLikeVisit(AfterClassLikeVisitEvent $event): void
    {
        Option::do(function() use ($event) {
            $typed_args = yield TypedArgGrabber::grab(
                fromEvent: $event,
                forSubClassOf: SumType::class,
                forMetaFunction: 'Klimick\Decode\Decoder\cases',
            );

            $storage = $event->getStorage();

            StorageManager::addMethod(
                named_as: '__construct',
                to: $storage,
                with_params: [
                    new FunctionLikeParameter(
                        name: 'case',
                        by_ref: false,
                        type: Type::combineUnionTypeArray(
                            union_types: $typed_args->map(fn($a) => $a->type)->toArray(),
                            codebase: null,
                        ),
                        is_optional: false,
                    ),
                ],
            );

            StorageManager::addMethod(
                named_as: 'match',
                to: $storage,
                with_params: $typed_args
                    ->map(fn($arg) => self::toMatcherParam($arg, $storage))
                    ->toArray(),
                with_templates: [
                    'TMatch' => [self::matchTemplateId($storage) => Type::getMixed()],
                ],
                with_return_type: self::matchReturnType($storage),
            );

            StorageManager::makeImmutable($storage);
        });
    }

    private static function toMatcherParam(TypedArg $typed_arg, ClassLikeStorage $storage): FunctionLikeParameter
    {
        return new FunctionLikeParameter(
            name: $typed_arg->name,
            by_ref: false,
            type: new Type\Union([
                new Type\Atomic\TCallable(
                    params: [$typed_arg->toParameterLike()],
                    return_type: self::matchReturnType($storage),
                ),
            ]),
            is_optional: false,
        );
    }
}
