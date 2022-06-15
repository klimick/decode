<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use Klimick\Decode\Error\DecodeError;
use ReflectionClass;
use function Fp\Cast\asList;
use function Fp\Collection\map;
use function Fp\Collection\unique;
use function Fp\Collection\groupMapReduce;

final class DefaultReporter
{
    private const INDEXED_ACCESS_WITHOUT_BRACKETS = "~\.(\d+)~";
    private const TO_INDEXED_ACCESS_WITH_BRACKETS = '[$1]';

    /**
     * @param non-empty-list<DecodeError> $errors
     */
    public static function report(array $errors, bool $useShortClassNames = false): ErrorReport
    {
        $typeErrors = [];
        $undefinedErrors = [];
        $constraintErrors = [];

        foreach ($errors as $error) {
            switch ($error->kind) {
                case DecodeError::KIND_TYPE_ERROR:
                    $lastErr = $error->context->lastEntry();

                    $typeErrors[] = new TypeErrorReport(
                        path: self::formatContextPath($error->context->path()),
                        expected: $useShortClassNames
                            ? self::formatExpectedType($lastErr->instance->name())
                            : $lastErr->instance->name(),
                        actual: $lastErr->actual,
                    );
                    break;

                case DecodeError::KIND_UNDEFINED_ERROR:
                    $undefinedErrors[] = new UndefinedErrorReport(
                        self::formatContextPath($error->context->path()),
                        $error->aliases,
                    );
                    break;

                case DecodeError::KIND_CONSTRAINT_ERRORS:
                    foreach ($error->constraintErrors as $e) {
                        $firstErr = $e->context->firstEntry();
                        $lastErr = $e->context->lastEntry();

                        $constraintErrors[] = new ConstraintErrorReport(
                            path: self::formatContextPath($e->context->path()),
                            value: $lastErr->actual,
                            meta: $firstErr->instance->metadata(),
                        );
                    }
                    break;
            }
        }

        return new ErrorReport([
            ...self::mergeTypeErrors($typeErrors),
            ...self::mergeUndefinedErrors($undefinedErrors),
            ...self::mergeConstraintErrors($constraintErrors),
        ]);
    }

    /**
     * @param list<TypeErrorReport> $errors
     * @return list<TypeErrorReport>
     */
    private static function mergeTypeErrors(array $errors): array
    {
        $grouped = groupMapReduce(
            $errors,
            fn(TypeErrorReport $e) => sprintf('%s-%s', $e->path, ActualValueToString::for($e->actual)),
            fn(TypeErrorReport $e) => [
                'path' => $e->path,
                'expected' => explode(' | ', $e->expected),
                'actual' => $e->actual,
            ],
            fn(array $lhs, array $rhs) => [
                'path' => $lhs['path'],
                'expected' => [...$lhs['expected'], ...$rhs['expected']],
                'actual' => $lhs['actual'],
            ],
        );

        return map(asList($grouped), fn(array $e) => new TypeErrorReport(
            path: $e['path'],
            expected: implode(' | ', array_unique($e['expected'])),
            actual: $e['actual']
        ));
    }

    /**
     * @param list<ConstraintErrorReport> $errors
     * @return list<ConstraintErrorReport>
     */
    private static function mergeConstraintErrors(array $errors): array
    {
        return unique($errors, fn(ConstraintErrorReport $e) => $e->toString());
    }

    /**
     * @param list<UndefinedErrorReport> $errors
     * @return list<UndefinedErrorReport>
     */
    private static function mergeUndefinedErrors(array $errors): array
    {
        $grouped = groupMapReduce(
            $errors,
            fn(UndefinedErrorReport $e) => $e->path,
            fn(UndefinedErrorReport $e) => [
                'path' => $e->path,
                'aliases' => $e->aliases,
            ],
            fn(array $lhs, array $rhs) => [
                'path' => $lhs['path'],
                'aliases' => [...$lhs['aliases'], ...$rhs['aliases']],
            ],
        );

        return map(asList($grouped), fn(array $e) => new UndefinedErrorReport(
            path: $e['path'],
            aliases: asList(array_unique($e['aliases'])),
        ));
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
