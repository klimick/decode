<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\DecoderInterface;

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

    public function __invoke(DecoderInterface|ConstraintInterface $name, mixed $actual, string|int $key = ''): self
    {
        return new self([...$this->entries, new ContextEntry($name->name(), $actual, (string) $key)]);
    }

    public function firstEntry(): ContextEntry
    {
        return $this->entries[0];
    }

    public function lastEntry(): ContextEntry
    {
        return $this->entries[count($this->entries) - 1];
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
