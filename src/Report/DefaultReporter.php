<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use ReflectionClass;
use Klimick\Decode\Context;
use Klimick\Decode\Invalid;
use Klimick\Decode\Internal\Shape\UndefinedProperty;

final class DefaultReporter
{
    public static function report(Invalid $invalid, bool $useShortClassNames = false): ErrorReport
    {
        $typeErrors = [];
        $undefinedProperty = [];

        foreach ($invalid->errors as $error) {
            $lastErr = $error->context->entries[count($error->context->entries) - 1];
            $propertyPath = self::pathFromContext($error->context);

            if ($lastErr->actual instanceof UndefinedProperty) {
                $undefinedProperty[] = $propertyPath;
                continue;
            }

            $typeErrors[] = new TypeError(
                path: $propertyPath,
                expected: $useShortClassNames
                    ? self::formatExpectedType($lastErr->name)
                    : $lastErr->name,
                actual: $lastErr->actual,
                payload: $error->payload
            );
        }

        return new ErrorReport($typeErrors, $undefinedProperty);
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
