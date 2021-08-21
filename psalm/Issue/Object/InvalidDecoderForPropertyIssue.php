<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue\Object;

use Psalm\Type;
use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;

final class InvalidDecoderForPropertyIssue extends CodeIssue
{
    public function __construct(
        string $property,
        Type\Union $actual_type,
        Type\Union $expected_type,
        CodeLocation $code_location,
    )
    {
        $actual_type_decoder_type_param = $actual_type->possibly_undefined
            ? "possibly-undefined|{$actual_type->getId()}>"
            : $actual_type->getId();

        parent::__construct(
            message: implode(' ', [
                "Invalid decoder for property '{$property}'.",
                "Expected: DecoderInterface<{$expected_type->getId()}>.",
                "Actual: DecoderInterface<{$actual_type_decoder_type_param}>.",
            ]),
            code_location: $code_location,
        );
    }
}
