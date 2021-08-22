<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\RuntimeData;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class InvalidRuntimeDataDefinitionIssue extends CodeIssue
{
    public function __construct(CodeLocation $code_location)
    {
        parent::__construct(
            message: implode(' ', [
                'RuntimeData::properties must return AbstractDecoder<array{...}>.',
                'Use shape(...) or partialShape(...).',
            ]),
            code_location: $code_location,
        );
    }
}
