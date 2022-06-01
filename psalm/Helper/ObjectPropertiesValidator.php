<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Fp\PsalmToolkit\Toolkit\PsalmApi;
use Klimick\PsalmDecode\Issue\Object\InvalidDecoderForPropertyIssue;
use Klimick\PsalmDecode\Issue\Object\NonexistentPropertyObjectPropertyIssue;
use Klimick\PsalmDecode\Issue\Object\RequiredObjectPropertyMissingIssue;
use Psalm\CodeLocation;
use Psalm\IssueBuffer;
use Psalm\StatementsSource;
use Psalm\Type;

final class ObjectPropertiesValidator
{
    /**
     * @param array<string, Type\Union> $actual_shape
     * @param array<string, Type\Union> $expected_shape
     * @param array<string, CodeLocation> $arg_code_locations
     */
    public function __construct(
        private StatementsSource $source,
        private array $actual_shape,
        private array $expected_shape,
        private CodeLocation $method_code_location,
        private array $arg_code_locations,
    ) {}

    public function validate(): void
    {
        $this->checkPropertyTypes();
        $this->checkNonexistentProperties();
        $this->checkMissingProperties();
    }

    private function checkPropertyTypes(): void
    {
        foreach ($this->expected_shape as $property => $type) {
            if (!array_key_exists($property, $this->actual_shape)) {
                continue;
            }

            if (PsalmApi::$types->isTypeContainedByType($this->actual_shape[$property], $type)) {
                continue;
            }

            $issue = new InvalidDecoderForPropertyIssue(
                property: $property,
                actual_type: $this->actual_shape[$property],
                expected_type: $type,
                code_location: $this->arg_code_locations[$property] ?? $this->method_code_location,
            );

            IssueBuffer::accepts($issue, $this->source->getSuppressedIssues());
        }
    }

    private function checkNonexistentProperties(): void
    {
        foreach (array_keys($this->actual_shape) as $property) {
            if (array_key_exists($property, $this->expected_shape)) {
                continue;
            }

            $issue = new NonexistentPropertyObjectPropertyIssue(
                property: $property,
                code_location: $this->arg_code_locations[$property] ?? $this->method_code_location,
            );

            IssueBuffer::accepts($issue, $this->source->getSuppressedIssues());
        }
    }

    private function checkMissingProperties(): void
    {
        $missing_properties = [];

        foreach (array_keys($this->expected_shape) as $property) {
            if (!array_key_exists($property, $this->actual_shape)) {
                $missing_properties[] = $property;
            }
        }

        if (!empty($missing_properties)) {
            $issue = new RequiredObjectPropertyMissingIssue(
                missing_properties: $missing_properties,
                code_location: $this->method_code_location,
            );

            IssueBuffer::accepts($issue, $this->source->getSuppressedIssues());
        }
    }
}
