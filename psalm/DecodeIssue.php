<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode;

use Psalm\CodeLocation;
use Psalm\Issue\CodeIssue;
use Psalm\Type;

final class DecodeIssue extends CodeIssue
{
    public static function invalidFromArgument(CodeLocation $code_location): self
    {
        return new self(
            message: implode(' ', [
                'Invalid argument for AbstractDecoder::from.',
                'Argument must be non-empty-string literal with "$." prefix or just "$"',
            ]),
            code_location: $code_location,
        );
    }

    public static function brandAlreadyDefined(string $brand, CodeLocation $code_location): self
    {
        return new self(
            message: "Decoder cannot be '{$brand}' multiple times.",
            code_location: $code_location,
        );
    }

    public static function usingDefaultAndOptionalHasNoSense(CodeLocation $code_location): self
    {
        return new self(
            message: 'Using AbstractDecoder::default and AbstractDecoder::optional at the same time has no sense.',
            code_location: $code_location,
        );
    }

    public static function incompatibleConstraints(
        Type\Union $constraints_type,
        Type\Union $decoder_type_parameter,
        CodeLocation $code_location,
    ): self
    {
        return new self(
            message: implode(' ', [
                "Value of type {$decoder_type_parameter->getId()}",
                "cannot be checked with constraints of type {$constraints_type->getId()}",
            ]),
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

    public static function invalidPropertyDecoder(
        string $property,
        Type\Union $actual_type,
        Type\Union $expected_type,
        CodeLocation $code_location,
    ): self
    {
        $actual_type_decoder_type_param = $actual_type->possibly_undefined
            ? "possibly-undefined|{$actual_type->getId()}>"
            : $actual_type->getId();

        return new self(
            message: implode(' ', [
                "Invalid decoder for property '{$property}'.",
                "Expected: DecoderInterface<{$expected_type->getId()}>.",
                "Actual: DecoderInterface<{$actual_type_decoder_type_param}>.",
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
