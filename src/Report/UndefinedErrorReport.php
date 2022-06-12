<?php

declare(strict_types=1);

namespace Klimick\Decode\Report;

use function implode;

final class UndefinedErrorReport
{
    public function __construct(
        /**
         * @psalm-readonly
         */
        public string $path,
        /**
         * @psalm-readonly
         * @var list<string>
         */
        public array $aliases = [],
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
