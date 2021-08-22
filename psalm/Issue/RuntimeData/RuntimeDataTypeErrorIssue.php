<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\RuntimeData;

use Klimick\Decode\Report\TypeErrorReport;
use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class RuntimeDataTypeErrorIssue extends CodeIssue
{
    public function __construct(TypeErrorReport $error, string $actual_type, CodeLocation $code_location)
    {
        parent::__construct(
            message: implode(' ', [
                "Wrong value at {$error->path}.",
                "Expected type: {$error->expected}.",
                "Actual type: {$actual_type}",
            ]),
            code_location: $code_location,
        );
    }
}
