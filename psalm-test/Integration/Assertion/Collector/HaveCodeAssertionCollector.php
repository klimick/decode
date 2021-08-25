<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use PhpParser\Node;
use Psalm\CodeLocation;
use Psalm\Type;
use Psalm\Type\Atomic\TClosure;
use function Fp\Collection\first;

final class HaveCodeAssertionCollector implements AssertionCollectorInterface
{
    public static function collect(Assertions $data, AssertionCollectingContext $context): Option
    {
        return Option::do(function() use ($data, $context) {
            $closure_code_location = yield self::getClosureCodeLocation($context);
            $closure_return_type = yield self::getClosureReturnType($context);

            return $data->with(
                new HaveCodeAssertionData($closure_code_location, $closure_return_type)
            );
        });
    }

    /**
     * @return Option<Type\Union>
     */
    private static function getClosureReturnType(AssertionCollectingContext $context): Option
    {
        return first($context->assertion_call->args)
            ->flatMap(fn($arg) => $context->getSingleAtomicType($arg->value))
            ->filter(fn($atomic) => $atomic instanceof TClosure)
            ->map(fn($atomic) => $atomic->return_type ?? Type::getVoid());
    }

    /**
     * @return Option<CodeLocation>
     */
    private static function getClosureCodeLocation(AssertionCollectingContext $context): Option
    {
        return first($context->assertion_call->args)
            ->map(fn($arg) => $arg->value)
            ->filter(fn($arg) => $arg instanceof Node\Expr\Closure || $arg instanceof Node\Expr\ArrowFunction)
            ->map(fn($arg) => new CodeLocation($context->event->getStatementsSource(), $arg));
    }

    public static function isSupported(AssertionCollectingContext $context): bool
    {
        return 'haveCode' === $context->assertion_name;
    }
}
