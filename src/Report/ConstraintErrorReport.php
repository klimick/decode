<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use function get_class;
use function is_object;
use function json_encode;
use function trim;
use const JSON_UNESCAPED_UNICODE;

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
        $actualValue = is_object($this->value)
            ? get_class($this->value) . '::class'
            : trim(json_encode($this->value, JSON_UNESCAPED_UNICODE), '"');

        $payload = json_encode($this->payload, JSON_UNESCAPED_UNICODE);

        return "[{$this->path}]: Value {$actualValue} cannot be validated with {$this->constraint}({$payload})";
    }
}
