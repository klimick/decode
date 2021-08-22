<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Klimick\Decode\Decoder\DecodeErrorInterface;
use Klimick\Decode\Decoder\UnionTypeErrors;
use ReflectionClass;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\TypeError;
use Klimick\Decode\Decoder\ConstraintsError;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Constraint\ConstraintError;

final class DefaultReporter
{
    private const INDEXED_ACCESS_WITHOUT_BRACKETS = "~\.(\d+)~";
    private const TO_INDEXED_ACCESS_WITH_BRACKETS = '[$1]';

    public static function report(Invalid $invalid, bool $useShortClassNames = false): ErrorReport
    {
        return self::reportErrors($invalid->errors, $useShortClassNames);
    }

    /**
     * @param non-empty-list<DecodeErrorInterface> $errors
     * @return ErrorReport
     */
    private static function reportErrors(array $errors, bool $useShortClassNames = false): ErrorReport
    {
        $typeErrors = [];
        $unionTypeErrors = [];
        $constraintErrors = [];
        $undefinedErrors = [];

        foreach ($errors as $error) {
            if ($error instanceof TypeError) {
                $typeErrors[] = self::reportTypeError($error, $useShortClassNames);
            } elseif ($error instanceof UnionTypeErrors) {
                $e = [];

                foreach ($error->errors as $typename => $errors) {
                    $e[$typename] = self::reportErrors($errors, $useShortClassNames);
                }

                $unionTypeErrors[] = $e;
            } elseif ($error instanceof ConstraintsError) {
                $constraintErrors = [
                    ...$constraintErrors,
                    ...array_map(fn($e) => self::reportConstraintError($e), $error->errors),
                ];
            } elseif ($error instanceof UndefinedError) {
                $undefinedErrors[] = self::reportUndefinedError($error);
            }
        }

        return new ErrorReport($typeErrors, $unionTypeErrors, $constraintErrors, $undefinedErrors);
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

    private static function reportUndefinedError(UndefinedError $error): UndefinedErrorReport
    {
        $size = count($error->context->entries);

        $last = $error->context->entries[$size - 1];
        $rest = array_slice($error->context->entries, offset: 0, length: $size - 1);

        $path = !empty($rest)
            ? self::pathFromContext(new Context($rest))
            : '$';

        return new UndefinedErrorReport($path, $last->key);
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
        $pathParts = [];

        foreach ($context->entries as $entry) {
            if ('' !== $entry->key) {
                $pathParts[] = $entry->key;
            }
        }

        return preg_replace(
            self::INDEXED_ACCESS_WITHOUT_BRACKETS,
            self::TO_INDEXED_ACCESS_WITH_BRACKETS,
            implode('.', ['$', ...$pathParts]),
        );
    }
}
