<?php

declare(strict_types=1);

namespace Klimick\PsalmTest\StaticType;

use Psalm\Type;

/**
 * @template T of scalar
 * @implements StaticTypeInterface<T>
 */
final class StaticLiteralType implements StaticTypeInterface
{
    /**
     * @param T $literal
     */
    public function __construct(private mixed $literal)
    {
    }

    /**
     * @psalm-suppress DocblockTypeContradiction
     * @psalm-suppress MixedArgument
     */
    public function toPsalm(): Type\Union
    {
        return match (true) {
            is_int($this->literal) => Type::getInt(value: $this->literal),
            is_float($this->literal) => Type::getFloat(value: $this->literal),
            is_string($this->literal) => Type::getString(value: $this->literal),
            is_bool($this->literal) => $this->literal ? Type::getTrue() : Type::getFalse(),
        };
    }
}
