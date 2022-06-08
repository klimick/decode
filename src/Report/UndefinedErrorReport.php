<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use function implode;

final class UndefinedErrorReport
{
    /**
     * @param list<string> $aliases
     */
    public function __construct(
        public string $path,
        public array $aliases,
    ) {}

    public function toString(): string
    {
        if (!empty($this->aliases)) {
            $aliases = implode(', ', $this->aliases);
            return "[{$this->path}]: Property is undefined (checked aliases: {$aliases})";
        }

        return "[{$this->path}]: Property is undefined";
    }
}
