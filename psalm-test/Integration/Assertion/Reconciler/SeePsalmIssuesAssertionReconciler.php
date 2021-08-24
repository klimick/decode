<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Reconciler;

use Fp\Functional\Option\Option;
use Klimick\PsalmTest\Integration\Assertion\Assertions;
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
            $unhandledIssues = self::handle($seePsalmIssues);

            return yield !empty($unhandledIssues)
                ? Option::some(new SeePsalmIssueAssertionFailed($unhandledIssues, $seePsalmIssues->code_location))
                : Option::none();
        });
    }

    /**
     * @return list<SeePsalmIssue>
     */
    private static function handle(SeePsalmIssuesData $seePsalmIssues): array
    {
        $issuesData = IssueBuffer::getIssuesData();

        // Treat all issues as unhandled if no actual issues for analysed file
        if (!array_key_exists($seePsalmIssues->code_location->file_path, $issuesData)) {
            return $seePsalmIssues->issues;
        }

        // All expected issues
        $expectedIssues = reindex(
            $seePsalmIssues->issues,
            fn(SeePsalmIssue $i) => "[{$i->type}]:[{$i->message}]",
        );

        // All actual issues from Psalm buffer
        $actualIssues = reindex(
            $issuesData[$seePsalmIssues->code_location->file_path],
            fn(IssueData $i) => "[{$i->type}]:[{$i->message}]",
        );

        // Catch all expected issues from buffer
        $handledIssues = filter(
            $actualIssues,
            fn(IssueData $i) => array_key_exists("[{$i->type}]:[{$i->message}]", $expectedIssues),
            preserveKeys: true
        );

        // Remove found expected issues from Psalm buffer
        foreach ($handledIssues as $i) {
            IssueBuffer::remove($i->file_path, $i->type, $i->from);
        }

        // Rest expected issues treat as unhandled
        return filter(
            $expectedIssues,
            fn(SeePsalmIssue $i) => !array_key_exists("[{$i->type}]:[{$i->message}]", $handledIssues),
        );
    }
}
