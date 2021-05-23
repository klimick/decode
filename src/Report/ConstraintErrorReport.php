<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

final class ConstraintErrorReport
{
    public function __construct(
        public string $path,
        public string $constraint,
        public mixed $value,
        public array $payload,
    ) { }
}
