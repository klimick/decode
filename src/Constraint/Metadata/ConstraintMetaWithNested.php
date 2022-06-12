<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint\Metadata;

/**
 * @psalm-immutable
 */
final class ConstraintMetaWithNested implements ConstraintMetaInterface
{
    /**
     * @param non-empty-string $name
     * @param non-empty-list<ConstraintMetaInterface>|ConstraintMetaInterface $nested
     */
    private function __construct(
        public string $name,
        public array|ConstraintMetaInterface $nested,
    ) {}

    /**
     * @param non-empty-string $name
     * @param non-empty-list<ConstraintMetaInterface>|ConstraintMetaInterface $nested
     * @psalm-pure
     */
    public static function of(string $name, array|ConstraintMetaInterface $nested): self
    {
        return new self($name, $nested);
    }

    public function name(): string
    {
        return $this->name;
    }
}
