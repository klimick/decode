<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Psalm\Type;

final class IncompatibleConstraint extends CodeIssue
{
    public function __construct(
        int $arg_offset,
        Type\Union $expected,
        Type\Union $actual,
        CodeLocation $code_location,
    )
    {
        parent::__construct(
            message: "Argument {$arg_offset} expects {$expected->getId()}, {$actual->getId()} provided",
            code_location: $code_location,
        );
    }
}
