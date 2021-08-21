<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Psalm\Type;

final class DecodeIssue extends CodeIssue
{
    public static function undefinedPropertyFetch(CodeLocation $code_location, string $runtime_data_class, string $property_id): self
    {
        return new self(
            message: "Property '{$property_id}' is not present in '{$runtime_data_class}' instance",
            code_location: $code_location,
        );
    }

    public static function couldNotAnalyzeOfCall(CodeLocation $code_location): self
    {
        return new self(
            message: 'RuntimeData::of call could not be analyzed because array value is not literal',
            code_location: $code_location
        );
    }

    /**
     * @param non-empty-list<string> $properties
     */
    public static function intersectionCollision(array $properties, CodeLocation $code_location): self
    {
        $duplicate = implode(', ', array_map(fn($p) => "'{$p}'",$properties));

        $message = count($properties) > 1
            ? "Intersection collision: properties {$duplicate} defined more than once."
            : "Intersection collision: property {$duplicate} defined more than once.";

        return new self(
            message: $message,
            code_location: $code_location,
        );
    }

    public static function invalidRuntimeDataDefinition(
        Type\Union $expected_decoder_type,
        Type\Union $return_decoder_type,
        CodeLocation $code_location,
    ): self
    {
        return new self(
            message: implode('', [
                "The declared return type '{$expected_decoder_type->getId()}' is incorrect, ",
                "got '{$return_decoder_type->getId()}'",
            ]),
            code_location: $code_location,
        );
    }
}
