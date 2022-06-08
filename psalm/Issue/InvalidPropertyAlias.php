<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class InvalidPropertyAlias extends CodeIssue
{
    public function __construct(CodeLocation $code_location)
    {
        parent::__construct(
            message: implode(' ', [
                'Invalid argument for DecoderInterface::from.',
                'Argument must be non-empty-string literal with "$." prefix or just "$"',
            ]),
            code_location: $code_location,
        );
    }
}
