<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class NotPartialProperty extends CodeIssue
{
    public function __construct(string $property, CodeLocation $code_location)
    {
        parent::__construct(
            message: sprintf('Property "%s" must be nullable in source class.', $property),
            code_location: $code_location,
        );
    }
}
