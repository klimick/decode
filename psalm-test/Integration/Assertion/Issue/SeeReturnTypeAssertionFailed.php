<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Issue;

use Klimick\PsalmTest\Integration\Assertion\Collector\HaveCodeAssertionData;
use Klimick\PsalmTest\Integration\Assertion\Collector\SeeReturnTypeAssertionData;
use Psalm\Issue\CodeIssue;

final class SeeReturnTypeAssertionFailed extends CodeIssue
{
    public function __construct(HaveCodeAssertionData $haveCodeAssertionData, SeeReturnTypeAssertionData $seeReturnTypeAssertionData)
    {
        parent::__construct(
            message: implode(' ', [
                "Actual return type: {$haveCodeAssertionData->actual_return_type->getId()},",
                "Expected return type: {$seeReturnTypeAssertionData->expected_return_type->getId()}",
            ]),
            code_location: $seeReturnTypeAssertionData->code_location,
        );
    }
}
