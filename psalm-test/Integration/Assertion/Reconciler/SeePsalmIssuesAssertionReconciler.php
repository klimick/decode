<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Reconciler;

use Closure;
use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
use Klimick\PsalmTest\Integration\Assertion\Collector\HaveCodeAssertionData;
use Klimick\PsalmTest\Integration\Assertion\Collector\SeePsalmIssue;
use Klimick\PsalmTest\Integration\Assertion\Collector\SeePsalmIssuesData;
use Klimick\PsalmTest\Integration\Assertion\Issue\SeePsalmIssueAssertionFailed;
use Psalm\Internal\Analyzer\IssueData;
use Psalm\IssueBuffer;
use function Fp\Collection\filter;
use function Fp\Collection\reindex;

final class SeePsalmIssuesAssertionReconciler implements AssertionReconcilerInterface
{
    public static function reconcile(Assertions $data): Option
    {
        return Option::do(function() use ($data) {
            $seePsalmIssues = yield $data(SeePsalmIssuesData::class);
            $haveCodeAssertion = yield $data(HaveCodeAssertionData::class);

            $unhandledIssues = self::handle($seePsalmIssues, $haveCodeAssertion);

            return yield !empty($unhandledIssues)
                ? Option::some(new SeePsalmIssueAssertionFailed($unhandledIssues, $seePsalmIssues->code_location))
                : Option::none();
        });
    }

    /**
     * @return list<SeePsalmIssue>
     */
    private static function handle(SeePsalmIssuesData $seePsalmIssues, HaveCodeAssertionData $haveCodeAssertion): array
    {
        $issuesData = IssueBuffer::getIssuesData();

        if (!array_key_exists($seePsalmIssues->code_location->file_path, $issuesData)) {
            return $seePsalmIssues->issues;
        }

        $expectedIssues = reindex($seePsalmIssues->issues, self::toKeyFn());
        $actualIssues = reindex($issuesData[$seePsalmIssues->code_location->file_path], self::toKeyFn());
        $handledIssues = filter($actualIssues, self::isHandled($haveCodeAssertion, $expectedIssues), preserveKeys: true);

        foreach ($handledIssues as $i) {
            IssueBuffer::remove($i->file_path, $i->type, $i->from);
        }

        return filter($expectedIssues, fn(SeePsalmIssue $i) => !array_key_exists(self::toKey($i), $handledIssues));
    }

    /**
     * @return Closure(object{type: string, message: string}): string
     */
    private static function toKeyFn(): Closure
    {
        return fn(object $i) => self::toKey($i);
    }

    /**
     * @param object{type: string, message: string} $i
     * @return non-empty-string
     */
    private static function toKey(object $i): string
    {
        return "[{$i->type}]:[{$i->message}]";
    }

    /**
     * @param array<string, SeePsalmIssue> $expectedIssues
     * @return Closure(IssueData): bool
     */
    private static function isHandled(HaveCodeAssertionData $haveCodeAssertion, array $expectedIssues): Closure
    {
        return fn(IssueData $i) => array_key_exists(self::toKey($i), $expectedIssues) &&
            $i->from >= $haveCodeAssertion->code_location->raw_file_start &&
            $i->to <= $haveCodeAssertion->code_location->raw_file_end;
    }
}
