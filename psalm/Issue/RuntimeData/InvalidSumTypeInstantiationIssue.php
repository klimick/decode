<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\RuntimeData;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Psalm\Type\Union;

final class InvalidSumTypeInstantiationIssue extends CodeIssue
{
    public function __construct(Union $expected_type, Union $actual_type, CodeLocation $code_location)
    {
        parent::__construct(
            message: sprintf('Expected type: %s. Actual type: %s.', $expected_type->getId(), $actual_type->getId()),
            code_location: $code_location,
        );
    }
}
