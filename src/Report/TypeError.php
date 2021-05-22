<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

/**
 * @psalm-immutable
 */
final class TypeError
{
    public function __construct(
        public string $path,
        public string $expected,
        public mixed $actual,
        public array $payload,
    ) { }
}
