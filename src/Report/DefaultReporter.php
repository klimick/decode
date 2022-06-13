<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Klimick\Decode\Error\ConstraintError;
use Klimick\Decode\Error\DecodeError;
use ReflectionClass;
use function array_merge;
use function Fp\Collection\map;

final class DefaultReporter
{
    private const INDEXED_ACCESS_WITHOUT_BRACKETS = "~\.(\d+)~";
    private const TO_INDEXED_ACCESS_WITH_BRACKETS = '[$1]';

    /**
     * @param non-empty-list<DecodeError> $errors
     */
    public static function report(array $errors, bool $useShortClassNames = false): ErrorReport
    {
        $typeErrors = map($errors, fn($error) => match ($error->kind) {
            DecodeError::KIND_TYPE_ERROR => [
                self::reportDecodeError($error, $useShortClassNames),
            ],
            DecodeError::KIND_UNDEFINED_ERROR => [
                new UndefinedErrorReport(
                    self::formatContextPath($error->context->path()),
                    $error->aliases,
                ),
            ],
            DecodeError::KIND_CONSTRAINT_ERRORS => map(
                $error->constraintErrors,
                fn($e) => self::reportConstraintError($e),
            )
        });

        return new ErrorReport(array_merge(...$typeErrors));
    }

    private static function reportConstraintError(ConstraintError $error): ConstraintErrorReport
    {
        $firstErr = $error->context->firstEntry();
        $lastErr = $error->context->lastEntry();

        return new ConstraintErrorReport(
            path: self::formatContextPath($error->context->path()),
            value: $lastErr->actual,
            meta: $firstErr->instance->metadata(),
        );
    }

    private static function reportDecodeError(DecodeError $error, bool $useShortClassNames): TypeErrorReport|UndefinedErrorReport
    {
        $lastErr = $error->context->lastEntry();

        return new TypeErrorReport(
            path: self::formatContextPath($error->context->path()),
            expected: $useShortClassNames
                ? self::formatExpectedType($lastErr->instance->name())
                : $lastErr->instance->name(),
            actual: $lastErr->actual,
        );
    }

    private static function formatExpectedType(string $union): string
    {
        $formatted = map(explode(' | ', $union), fn($atomic) => class_exists($atomic)
            ? (new ReflectionClass($atomic))->getShortName()
            : $atomic);

        return implode(' | ', $formatted);
    }

    private static function formatContextPath(string $path): string
    {
        $path = preg_replace(
            self::INDEXED_ACCESS_WITHOUT_BRACKETS,
            self::TO_INDEXED_ACCESS_WITH_BRACKETS,
            $path,
        );

        return $path === '$.__root_value__' ? '$' : $path;
    }
}
