<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use Psalm\Type\Atomic\TClosure;
use function Fp\Collection\first;

final class HaveCodeAssertionCollector implements AssertionCollectorInterface
{
    public static function collect(Assertions $data, AssertionCollectingContext $context): Option
    {
        return first($context->assertion_call->args)
            ->flatMap(fn($arg) => $context->getSingleAtomicType($arg->value))
            ->filter(fn($atomic) => $atomic instanceof TClosure)
            ->flatMap(fn($atomic) => Option::fromNullable($atomic->return_type))
            ->map(fn($actual_return_type) => $data->with(
                new HaveCodeAssertionData($context->getCodeLocation(), $actual_return_type)
            ));
    }

    public static function isSupported(AssertionCollectingContext $context): bool
    {
        return 'haveCode' === $context->assertion_name;
    }
}
