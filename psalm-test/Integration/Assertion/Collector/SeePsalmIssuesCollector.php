<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Closure;
use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use Klimick\PsalmTest\Integration\Psalm;
use Psalm\Type;
use Psalm\Type\Atomic\TLiteralString;
use function Fp\Collection\at;

final class SeePsalmIssuesCollector implements AssertionCollectorInterface
{
    public static function collect(Assertions $data, AssertionCollectingContext $context): Option
    {
        return Option::do(function() use ($data, $context) {
            $issue_type = yield self::getSeePsalmIssueArg($context, position: 0)
                ->flatMap(self::getLiteralStringValue());

            $issue_message = yield self::getSeePsalmIssueArg($context, position: 1)
                ->flatMap(self::getLiteralStringValue())
                ->map(fn($value) => strtr($value, self::getFormattingArgs($context)));

            $code_location = $context->getCodeLocation();

            return $data->with(
                $data(SeePsalmIssuesData::class)
                    ->getOrElse(SeePsalmIssuesData::empty($code_location))
                    ->concat(new SeePsalmIssue($issue_type, $issue_message))
            );
        });
    }

    /**
     * @return array<string, string>
     */
    private static function getFormattingArgs(AssertionCollectingContext $context): array
    {
        $formatting_args = Option::do(function() use ($context) {
            $issue_args = yield self::getSeePsalmIssueArg($context, position: 2)
                ->flatMap(Psalm::asSingleAtomicOf(Type\Atomic\TKeyedArray::class));

            $replacements = [];

            foreach ($issue_args->properties as $name => $property) {
                $replacements["#[{$name}]"] = yield self::getLiteralStringValue()($property);
            }

            return $replacements;
        });

        return $formatting_args->getOrElse([]);
    }

    /**
     * @return Closure(Type\Union): Option<string>
     */
    private static function getLiteralStringValue(): Closure
    {
        return fn(Type\Union $atomic) => Option::some($atomic)
            ->flatMap(Psalm::asSingleAtomicOf(TLiteralString::class))
            ->map(fn($atomic) => $atomic->value);
    }

    /**
     * @return Option<Type\Union>
     */
    private static function getSeePsalmIssueArg(AssertionCollectingContext $context, int $position): Option
    {
        return at($context->assertion_call->args, $position)->flatMap(Psalm::getArgType($context->event));
    }

    public static function isSupported(AssertionCollectingContext $context): bool
    {
        return 'seePsalmIssue' === $context->assertion_name;
    }
}
