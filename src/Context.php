<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\DecoderInterface;
use function array_key_first;
use function array_key_last;

/**
 * @psalm-immutable
 */
final class Context
{
    /**
     * @param non-empty-list<ContextEntry> $entries
     */
    private function __construct(public array $entries) { }

    /**
     * @psalm-pure
     */
    public static function root(DecoderInterface|ConstraintInterface $instance, mixed $actual, int|string $key = '$'): self
    {
        return new self([
            new ContextEntry($instance, $actual, (string) $key),
        ]);
    }

    public function __invoke(DecoderInterface|ConstraintInterface $instance, mixed $actual, string|int $key = ''): self
    {
        return new self([...$this->entries, new ContextEntry($instance, $actual, (string) $key)]);
    }

    public function firstEntry(): ContextEntry
    {
        $firstKey = array_key_first($this->entries);
        return $this->entries[$firstKey];
    }

    public function lastEntry(): ContextEntry
    {
        $lastKey = array_key_last($this->entries);
        return $this->entries[$lastKey];
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
