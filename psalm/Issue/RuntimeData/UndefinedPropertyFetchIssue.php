<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\RuntimeData;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class UndefinedPropertyFetchIssue extends CodeIssue
{
    public function __construct(CodeLocation $code_location, string $runtime_data_class, string $property_id)
    {
        parent::__construct(
            message: sprintf('Property "%s" is not present in "%s" instance.', $property_id, $runtime_data_class),
            code_location: $code_location,
        );
    }
}
