<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Klimick\Decode\ContextEntry;
use Klimick\Decode\Decoder\DecodeErrorInterface;
use ReflectionClass;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\TypeError;
use Klimick\Decode\Decoder\ConstraintsError;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Constraint\ConstraintError;
use function Fp\Collection\map;

final class DefaultReporter
{
    private const INDEXED_ACCESS_WITHOUT_BRACKETS = "~\.(\d+)~";
    private const TO_INDEXED_ACCESS_WITH_BRACKETS = '[$1]';

    /**
     * @param non-empty-list<DecodeErrorInterface> $errors
     */
    public static function report(array $errors, bool $useShortClassNames = false): ErrorReport
    {
        return self::reportErrors($errors, $useShortClassNames);
    }

    /**
     * @param non-empty-list<DecodeErrorInterface> $errors
     * @return ErrorReport
     */
    private static function reportErrors(array $errors, bool $useShortClassNames = false): ErrorReport
    {
        $typeErrors = [];
        $constraintErrors = [];
        $undefinedErrors = [];

        foreach ($errors as $error) {
            if ($error instanceof TypeError) {
                $typeErrors[] = self::reportTypeError($error, $useShortClassNames);
            } elseif ($error instanceof ConstraintsError) {
                foreach ($error->errors as $e) {
                    $constraintErrors[] = self::reportConstraintError($e);
                }
            } elseif ($error instanceof UndefinedError) {
                $undefinedErrors[] = self::pathFromContext($error->context);
            }
        }

        return new ErrorReport($typeErrors, $constraintErrors, $undefinedErrors);
    }

    private static function reportConstraintError(ConstraintError $error): ConstraintErrorReport
    {
        return new ConstraintErrorReport(
            path: self::pathFromContext($error->context),
            constraint: $error->context->firstEntry()->name,
            value: self::actualValue($error->context->lastEntry()),
            payload: $error->payload,
        );
    }

    private static function reportTypeError(TypeError $error, bool $useShortClassNames): TypeErrorReport
    {
        $lastErr = $error->context->lastEntry();

        return new TypeErrorReport(
            path: self::pathFromContext($error->context),
            expected: $useShortClassNames
                ? self::formatExpectedType($lastErr->name)
                : $lastErr->name,
            actual: self::actualValue($lastErr),
        );
    }

    private static function actualValue(ContextEntry $entry): mixed
    {
        return is_string($entry->actual) ? "'{$entry->actual}'" : $entry->actual;
    }

    private static function formatExpectedType(string $union): string
    {
        $formatted = map(explode(' | ', $union), fn($atomic) => class_exists($atomic)
            ? (new ReflectionClass($atomic))->getShortName()
            : $atomic);

        return implode(' | ', $formatted);
    }

    private static function pathFromContext(Context $context): string
    {
        return preg_replace(
            self::INDEXED_ACCESS_WITHOUT_BRACKETS,
            self::TO_INDEXED_ACCESS_WITH_BRACKETS,
            $context->path(),
        );
    }
}
