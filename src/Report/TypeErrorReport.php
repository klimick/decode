<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

/**
 * @psalm-immutable
 */
final class TypeErrorReport
{
    public function __construct(
        public string $path,
        public string $expected,
        public mixed $actual,
    ) { }
}
