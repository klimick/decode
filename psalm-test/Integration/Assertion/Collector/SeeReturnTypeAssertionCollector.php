<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use Klimick\PsalmTest\Integration\Psalm;
use Klimick\PsalmTest\StaticType\StaticTypeInterface;
use Psalm\Type;
use Psalm\Type\Atomic\TFalse;
use Psalm\Type\Atomic\TGenericObject;
use Psalm\Type\Atomic\TTrue;
use function Fp\Collection\first;
use function Fp\Collection\second;

final class SeeReturnTypeAssertionCollector implements AssertionCollectorInterface
{
    public static function collect(Assertions $data, AssertionCollectingContext $context): Option
    {
        return Option::do(function() use ($data, $context) {
            $expected_return_type = yield self::getExpectedReturnType($context);
            $invariant_compare = yield self::isInvariantCompare($context);

            return $data->with(
                new SeeReturnTypeAssertionData($context->getCodeLocation(), $expected_return_type, $invariant_compare)
            );
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function getExpectedReturnType(AssertionCollectingContext $context): Option
    {
        return first($context->assertion_call->args)
            ->flatMap(Psalm::getArgType($context->event))
            ->flatMap(Psalm::asSingleAtomicOf(TGenericObject::class))
            ->flatMap(Psalm::getTypeParam(StaticTypeInterface::class, position: 0));
    }

    /**
     * @return Option<bool>
     */
    private static function isInvariantCompare(AssertionCollectingContext $context): Option
    {
        return second($context->assertion_call->args)
            ->flatMap(Psalm::getArgType($context->event))
            ->flatMap(Psalm::asSingleAtomic())
            ->filter(fn($atomic) => $atomic instanceof TTrue || $atomic instanceof TFalse)
            ->map(fn($atomic) => match(true) {
                $atomic instanceof TTrue => true,
                $atomic instanceof TFalse => false,
            })
            ->orElse(fn() => Option::some(true));
    }

    public static function isSupported(AssertionCollectingContext $context): bool
    {
        return 'seeReturnType' === $context->assertion_name;
    }
}
