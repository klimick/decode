<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\Integration\Assertion\Issue;

use Klimick\PsalmTest\Integration\Assertion\Collector\HaveCodeAssertionData;
use Klimick\PsalmTest\Integration\Assertion\Collector\SeeReturnTypeAssertionData;
use Psalm\Issue\CodeIssue;

final class SeeReturnTypeAssertionFailed extends CodeIssue
{
    public function __construct(HaveCodeAssertionData $haveCodeAssertion, SeeReturnTypeAssertionData $seeReturnTypeAssertion)
    {
        parent::__construct(
            message: implode(' ', [
                "Actual return type: {$haveCodeAssertion->actual_return_type->getId()},",
                "Expected return type: {$seeReturnTypeAssertion->expected_return_type->getId()}",
            ]),
            code_location: $seeReturnTypeAssertion->code_location,
        );
    }
}
