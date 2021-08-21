<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\HighOrder;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class BrandAlreadyDefinedIssue extends CodeIssue
{
    public function __construct(string $brand, CodeLocation $code_location)
    {
        parent::__construct(
            message: "Method '{$brand}' should not called multiple times.",
            code_location: $code_location,
        );
    }
}
