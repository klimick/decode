<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\Object;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class NotPartialPropertyIssue extends CodeIssue
{
    public function __construct(string $property, CodeLocation $code_location)
    {
        parent::__construct(
            message: "Property '{$property}' must be nullable in source class.",
            code_location: $code_location,
        );
    }
}
