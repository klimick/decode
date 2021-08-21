<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\HighOrder;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class OptionalCallContradictionIssue extends CodeIssue
{
    public function __construct(CodeLocation $code_location)
    {
        parent::__construct(
            message: 'Using AbstractDecoder::default and AbstractDecoder::optional at the same time has no sense.',
            code_location: $code_location,
        );
    }
}
