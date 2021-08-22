<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\UnionRuntimeData;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class UnexhaustiveMatchIssue extends CodeIssue
{
    public function __construct(string $matcher, CodeLocation $code_location)
    {
        parent::__construct(
            message: "Match with name '{$matcher}' is not specified",
            code_location: $code_location,
        );
    }
}
