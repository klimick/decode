<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint\Metadata;

/**
 * @psalm-immutable
 */
final class ConstraintMetaWithPayload implements ConstraintMetaInterface
{
    /**
     * @param non-empty-string $name
     * @param non-empty-array<string, mixed> $payload
     */
    private function __construct(
        public string $name,
        public array $payload,
    ) {}

    /**
     * @param non-empty-string $name
     * @param non-empty-array<string, mixed> $payload
     * @psalm-pure
     */
    public static function of(string $name, array $payload): self
    {
        return new self($name, $payload);
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }
}
