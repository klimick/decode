<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Issue;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Klimick\PsalmTest\Integration\Assertion\Collector\SeePsalmIssue;
use function Fp\Collection\map;

final class SeePsalmIssueAssertionFailed extends CodeIssue
{
    /**
     * @param non-empty-list<SeePsalmIssue> $unhandledIssues
     */
    public function __construct(public array $unhandledIssues, CodeLocation $code_location)
    {
        $formatted = map($this->unhandledIssues, fn(SeePsalmIssue $i) => "Don't see expected issue [{$i->type}: {$i->message}]");

        parent::__construct(
            message: implode(PHP_EOL, $formatted),
            code_location: $code_location,
        );
    }
}
