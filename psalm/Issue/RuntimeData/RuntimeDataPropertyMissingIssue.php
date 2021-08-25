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
            message: sprintf('Required property "%s" at path %s is missing.', $error->property, $error->path),
            code_location: $code_location,
        );
    }
}
