<?php

declare(strict_types=1);

namespace Klimick\Decode;

/**
 * @psalm-immutable
 */
final class ContextEntry
{
    public function __construct(
        public string $name,
        public mixed $actual,
        public string $key,
    ) {}
}
