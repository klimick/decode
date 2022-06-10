<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use function get_class;
use function is_object;
use function is_string;
use function json_encode;
use const JSON_UNESCAPED_UNICODE;

final class ActualValueToString
{
    public static function for(mixed $value): string
    {
        return match (true) {
            is_string($value) => "'{$value}'",
            is_object($value) => get_class($value) . '::class',
            default => json_encode($value, JSON_UNESCAPED_UNICODE),
        };
    }
}
