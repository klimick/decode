<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use function class_exists;
use function get_class;
use function is_object;
use function json_encode;
use function trim;
use const JSON_UNESCAPED_UNICODE;

final class TypeErrorReport
{
    public function __construct(
        /** @psalm-readonly */
        public string $path,
        /** @psalm-readonly */
        public string $expected,
        /** @psalm-readonly */
        public mixed $actual,
    ) { }

    public function toString(): string
    {
        $actualValue = is_object($this->actual)
            ? get_class($this->actual) . '::class'
            : trim(json_encode($this->actual, JSON_UNESCAPED_UNICODE), '"');

        $expectedValue = class_exists($this->expected)
            ? $this->expected . '::class'
            : $this->expected;

        return "[{$this->path}]: Type error. Value {$actualValue} cannot be represented as {$expectedValue}";
    }
}
