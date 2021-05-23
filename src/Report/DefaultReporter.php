<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use ReflectionClass;
use Klimick\Decode\Context;
use Klimick\Decode\Invalid;
use Klimick\Decode\Error\TypeError;
use Klimick\Decode\Error\ConstraintError;
use Klimick\Decode\Error\UndefinedError;

final class DefaultReporter
{
    public static function report(Invalid $invalid, bool $useShortClassNames = false): ErrorReport
    {
        $typeErrors = [];
        $constraintErrors = [];
        $undefinedErrors = [];

        foreach ($invalid->errors as $error) {
            if ($error instanceof TypeError) {
                $typeErrors[] = self::reportTypeError($error, $useShortClassNames);
            }

            if ($error instanceof ConstraintError) {
                $constraintErrors[] = self::reportConstraintError($error);
            }

            if ($error instanceof UndefinedError) {
                $undefinedErrors[] = self::pathFromContext($error->context);
            }
        }

        return new ErrorReport($typeErrors, $constraintErrors, $undefinedErrors);
    }

    private static function reportConstraintError(ConstraintError $error): ConstraintErrorReport
    {
        $lastErr = $error->context->entries[count($error->context->entries) - 1];
        $propertyPath = self::pathFromContext($error->context);

        return new ConstraintErrorReport(
            path: $propertyPath,
            constraint: $error->constraint,
            value: $lastErr->actual,
            payload: $error->payload,
        );
    }

    private static function reportTypeError(TypeError $error, bool $useShortClassNames): TypeErrorReport
    {
        $lastErr = $error->context->entries[count($error->context->entries) - 1];
        $propertyPath = self::pathFromContext($error->context);

        return new TypeErrorReport(
            path: $propertyPath,
            expected: $useShortClassNames
                ? self::formatExpectedType($lastErr->name)
                : $lastErr->name,
            actual: $lastErr->actual,
        );
    }

    private static function formatExpectedType(string $type): string
    {
        $types = explode(' | ', $type);
        $formatted = [];

        foreach ($types as $type) {
            $formatted[] = class_exists($type)
                ? (new ReflectionClass($type))->getShortName()
                : $type;
        }

        return implode(' | ', $formatted);
    }

    private static function pathFromContext(Context $context): string
    {
        $pathParts = ['$'];

        foreach ($context->entries as $entry) {
            if ('' === $entry->key) {
                continue;
            }

            $pathParts[] = $entry->key;
        }

        return preg_replace("~\.(\d+)~", '[$1]', implode('.', $pathParts));
    }
}
