<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Issue;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Psalm\Type;

final class InvalidDecoderForProperty extends CodeIssue
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

        $message = implode(' ', [
            "Invalid decoder for property \"{$property}\".",
            "Expected: Klimick\Decode\Decoder\DecoderInterface<{$expected_type->getId()}>.",
            "Actual: Klimick\Decode\Decoder\DecoderInterface<{$actual_type_decoder_type_param}>.",
        ]);

        parent::__construct($message, $code_location);
    }
}
