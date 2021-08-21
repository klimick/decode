<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\ObjectDecoder;

use Klimick\PsalmDecode\Issue\Object\InvalidDecoderForPropertyIssue;
use Klimick\PsalmDecode\Issue\Object\NonexistentPropertyObjectPropertyIssue;
use Klimick\PsalmDecode\Issue\Object\RequiredObjectPropertyMissingIssue;
use Psalm\StatementsSource;
use Psalm\Type;
use Psalm\Codebase;
use Psalm\IssueBuffer;
use Psalm\CodeLocation;
use Psalm\Internal\Type\Comparator\UnionTypeComparator;

final class ObjectPropertiesValidator
{
    /**
     * @param array<string, Type\Union> $expected_shape
     * @param array<string, Type\Union> $actual_shape
     * @param array<string, CodeLocation> $arg_code_locations
     */
    public static function checkPropertyTypes(
        Codebase $codebase,
        StatementsSource $source,
        CodeLocation $method_code_location,
        array $arg_code_locations,
        array $expected_shape,
        array $actual_shape,
    ): void
    {
        foreach ($expected_shape as $property => $type) {
            if (!array_key_exists($property, $actual_shape)) {
                continue;
            }

            if (UnionTypeComparator::isContainedBy($codebase, $actual_shape[$property], $type)) {
                continue;
            }

            $issue = new InvalidDecoderForPropertyIssue(
                property: $property,
                actual_type: $actual_shape[$property],
                expected_type: $type,
                code_location: $arg_code_locations[$property] ?? $method_code_location,
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }
    }

    /**
     * @param array<string, Type\Union> $actual_shape
     * @param array<string, Type\Union> $expected_shape
     * @param array<string, CodeLocation> $arg_code_locations
     */
    public static function checkNonexistentProperties(
        array $actual_shape,
        array $expected_shape,
        array $arg_code_locations,
        StatementsSource $source,
        CodeLocation $method_code_location,
    ): void
    {
        foreach (array_keys($actual_shape) as $property) {
            if (array_key_exists($property, $expected_shape)) {
                continue;
            }

            $issue = new NonexistentPropertyObjectPropertyIssue(
                property: $property,
                code_location: $arg_code_locations[$property] ?? $method_code_location,
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }
    }

    /**
     * @param array<string, Type\Union> $expected_shape
     * @param array<string, Type\Union> $actual_shape
     */
    public static function checkMissingProperties(
        StatementsSource $source,
        CodeLocation $method_code_location,
        array $expected_shape,
        array $actual_shape,
    ): void
    {
        $missing_properties = [];

        foreach (array_keys($expected_shape) as $property) {
            if (!array_key_exists($property, $actual_shape)) {
                $missing_properties[] = $property;
            }
        }

        if (!empty($missing_properties)) {
            $issue = new RequiredObjectPropertyMissingIssue(
                missing_properties: $missing_properties,
                code_location: $method_code_location
            );

            IssueBuffer::accepts($issue, $source->getSuppressedIssues());
        }
    }
}
