<?php

declare(strict_types=1);

namespace Klimick\Decode;

use JsonSerializable;

/**
 * @psalm-immutable
 */
final class Context implements JsonSerializable
{
    /**
     * @param non-empty-list<ContextEntry> $entries
     */
    public function __construct(public array $entries) { }

    public function __invoke(string $name, mixed $actual, string $key = ''): self
    {
        return new self([...$this->entries, new ContextEntry($name, $actual, $key)]);
    }

    public function jsonSerialize()
    {
        return $this->entries;
    }
}
