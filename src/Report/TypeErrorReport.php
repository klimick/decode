<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use function class_exists;

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
        $actualValue = ActualValueToString::for($this->actual);

        $expectedValue = class_exists($this->expected)
            ? $this->expected . '::class'
            : $this->expected;

        return "[{$this->path}]: Type error. Value {$actualValue} cannot be represented as {$expectedValue}";
    }
}
