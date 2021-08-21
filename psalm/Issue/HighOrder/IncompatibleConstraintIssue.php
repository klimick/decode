<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\HighOrder;

use Psalm\Type;
use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class IncompatibleConstraintIssue extends CodeIssue
{
    public function __construct(
        Type\Union $constraints_type,
        Type\Union $decoder_type_parameter,
        CodeLocation $code_location,
    )
    {
        parent::__construct(
            message: implode(' ', [
                "Value of type {$decoder_type_parameter->getId()}",
                "cannot be checked with constraints of type {$constraints_type->getId()}",
            ]),
            code_location: $code_location,
        );
    }
}
