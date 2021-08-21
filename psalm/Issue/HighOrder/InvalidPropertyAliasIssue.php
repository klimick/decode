<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\HighOrder;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class InvalidPropertyAliasIssue extends CodeIssue
{
    public function __construct(CodeLocation $code_location)
    {
        parent::__construct(
            message: implode(' ', [
                'Invalid argument for AbstractDecoder::from.',
                'Argument must be non-empty-string literal with "$." prefix or just "$"',
            ]),
            code_location: $code_location,
        );
    }
}
