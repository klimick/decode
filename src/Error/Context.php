<?php

declare(strict_types=1);

namespace Klimick\Decode\Error;

use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\DecoderInterface;
use function array_key_first;
use function array_key_last;

/**
 * @psalm-type ContextFor = DecoderInterface|ConstraintInterface
 *
 * @template TFor of ContextFor
 * @psalm-immutable
 */
final class Context
{
    /**
     * @param non-empty-list<ContextEntry<TFor>> $entries
     */
    private function __construct(public array $entries) { }

    /**
     * @template TsFor of ContextFor
     *
     * @param TsFor $instance
     * @return Context<TsFor>
     *
     * @psalm-pure
     */
    public static function root(DecoderInterface|ConstraintInterface $instance, mixed $actual, int|string $key = '$'): self
    {
        return new self([
            new ContextEntry($instance, $actual, (string) $key),
        ]);
    }

    /**
     * @param TFor $instance
     * @return Context<TFor>
     */
    public function __invoke(DecoderInterface|ConstraintInterface $instance, mixed $actual, string|int $key = ''): self
    {
        return new self([...$this->entries, new ContextEntry($instance, $actual, (string) $key)]);
    }

    /**
     * @return ContextEntry<TFor>
     */
    public function firstEntry(): ContextEntry
    {
        $firstKey = array_key_first($this->entries);
        return $this->entries[$firstKey];
    }

    /**
     * @return ContextEntry<TFor>
     */
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
