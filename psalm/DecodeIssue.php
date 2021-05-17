<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Psalm\Type\Union;

final class DecodeIssue extends CodeIssue
{
    public static function invalidPropertyDecoder(
        string $property,
        Union $actual_type,
        Union $expected_type,
        CodeLocation $code_location,
    ): self
    {
        return new self(
            message: implode(' ', [
                "Invalid decoder for property '{$property}'.",
                "Expected: DecoderInterface<{$expected_type->getId()}>.",
                "Actual: DecoderInterface<{$actual_type->getId()}>.",
            ]),
            code_location: $code_location,
        );
    }

    /**
     * @param non-empty-list<string> $missing_properties
     */
    public static function requiredPropertiesMissed(array $missing_properties, CodeLocation $code_location): self
    {
        $names = implode(', ', $missing_properties);

        return new DecodeIssue(
            message: "Required decoders for properties missed: {$names}",
            code_location: $code_location,
        );
    }

    public static function nonexistentProperty(string $property, CodeLocation $code_location): self
    {
        return new DecodeIssue(
            message: "Property '{$property}' does not exist.",
            code_location: $code_location,
        );
    }

    public static function notPartialProperty(string $property, CodeLocation $code_location): self
    {
        return new DecodeIssue(
            message: "Property '{$property}' must be nullable in source class.",
            code_location: $code_location,
        );
    }
}
