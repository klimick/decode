<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\RuntimeData;

use Klimick\Decode\Report\UndefinedErrorReport;
use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class RuntimeDataPropertyMissingIssue extends CodeIssue
{
    public function __construct(UndefinedErrorReport $error, CodeLocation $code_location)
    {
        parent::__construct(
            message: "Required property '{$error->property}' at path {$error->path} is missing",
            code_location: $code_location,
        );
    }
}
