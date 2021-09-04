<?php

declare(strict_types=1);

namespace Klimick\Decode;

/**
 * @psalm-immutable
 */
final class Context
{
    /**
     * @param non-empty-list<ContextEntry> $entries
     */
    public function __construct(public array $entries) { }

    /**
     * @psalm-pure
     */
    public static function root(string $name, mixed $actual): self
    {
        return new self([
            new ContextEntry($name, $actual, '$'),
        ]);
    }

    public function __invoke(string $name, mixed $actual, string $key = ''): self
    {
        return new self([...$this->entries, new ContextEntry($name, $actual, $key)]);
    }

    public function path(): string
    {
        $pathParts = [];

        foreach ($this->entries as $entry) {
            if ('' !== $entry->key) {
                $pathParts[] = $entry->key;
            }
        }

        return implode('.', $pathParts);
    }
}
