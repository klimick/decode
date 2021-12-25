<?php

declare(strict_types=1);

namespace Klimick\PsalmDecode\Helper;

use Psalm\Storage\FunctionLikeParameter;
use Psalm\Type\Union;

/**
 * @psalm-immutable
 */
final class TypedArg
{
    public function __construct(
        public string $name,
        public Union $type,
    ) { }

    public function toParameterLike(): FunctionLikeParameter
    {
        return new FunctionLikeParameter(name: $this->name, by_ref: false, type: $this->type, is_optional: false);
    }

    public function withType(Union $type): self
    {
        return new self($this->name, $type);
    }
}
