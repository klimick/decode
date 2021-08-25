<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\UnionRuntimeData;

use Psalm\Type;
use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class InvalidMatcherTypeIssue extends CodeIssue
{
    public function __construct(Type\Union $expected_matcher_type, Type\Union $actual_matcher_type, CodeLocation $code_location)
    {
        parent::__construct(
            message: implode(' ', [
                "Invalid matcher type given.",
                "Expected type: {$expected_matcher_type->getId()}.",
                "Actual type: {$actual_matcher_type->getId()}.",
            ]),
            code_location: $code_location,
        );
    }
}
