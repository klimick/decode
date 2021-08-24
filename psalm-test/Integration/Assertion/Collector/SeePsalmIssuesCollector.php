<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Collector;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use Psalm\Type\Atomic\TLiteralString;
use function Fp\Collection\at;

final class SeePsalmIssuesCollector implements AssertionCollectorInterface
{
    public static function collect(Assertions $data, AssertionCollectingContext $context): Option
    {
        return Option::do(function() use ($data, $context) {
            $issue_type = yield self::getSeePsalmIssueArg($context, position: 0);
            $issue_message = yield self::getSeePsalmIssueArg($context, position: 1);
            $code_location = $context->getCodeLocation();

            return $data->with(
                $data(SeePsalmIssuesData::class)
                    ->getOrElse(SeePsalmIssuesData::empty($code_location))
                    ->concat(new SeePsalmIssue($issue_type, $issue_message))
            );
        });
    }

    /**
     * @return Option<string>
     */
    private static function getSeePsalmIssueArg(AssertionCollectingContext $context, int $position): Option
    {
        return at($context->assertion_call->args, $position)
            ->flatMap(fn($arg) => $context->getSingleAtomicType($arg->value))
            ->filter(fn($atomic) => $atomic instanceof TLiteralString)
            ->map(fn($atomic) => $atomic->value);
    }

    public static function isSupported(AssertionCollectingContext $context): bool
    {
        return 'seePsalmIssue' === $context->assertion_name;
    }
}
