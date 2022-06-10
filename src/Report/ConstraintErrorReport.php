<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

final class ConstraintErrorReport
{
    public function __construct(
        /** @psalm-readonly */
        public string $path,
        /** @psalm-readonly */
        public string $constraint,
        /** @psalm-readonly */
        public mixed $value,
        /** @psalm-readonly */
        public array $payload,
    ) { }

    public function toString(): string
    {
        $actualValue = ActualValueToString::for($this->value);
        $payload = ActualValueToString::for($this->payload);

        return "[{$this->path}]: Value {$actualValue} cannot be validated with {$this->constraint}({$payload})";
    }
}
