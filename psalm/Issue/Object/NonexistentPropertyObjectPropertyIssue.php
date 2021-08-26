<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\Object;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class NonexistentPropertyObjectPropertyIssue extends CodeIssue
{
    public function __construct(string $property, CodeLocation $code_location)
    {
        parent::__construct(
            message: sprintf('Property "%s" does not exist.', $property),
            code_location: $code_location,
        );
    }
}
