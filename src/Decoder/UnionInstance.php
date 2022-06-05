<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\TaggedUnionDecoder;
use Klimick\Decode\Internal\UnionDecoder;
use RuntimeException;

/**
 * @psalm-require-implements InferUnion
 */
trait UnionInstance
{
    /**
     * @internal
     */
    final public function __construct(
        private mixed $instance,
        private int $offset,
    ) {}

    final public function __get(string $name): mixed
    {
        if ($name === 'value') {
            return $this->instance;
        }

        throw new RuntimeException("Property '{$name}' is not defined! Did you mean 'value'?");
    }

    final public function __call(string $name, array $arguments): mixed
    {
        if ('is' === $name) {
            /** @psalm-suppress PossiblyUndefinedIntArrayOffset */
            return $this->instance instanceof $arguments[0];
        }

        if ('match' === $name) {
            /** @psalm-suppress MixedFunctionCall */
            return ($arguments[$this->offset])($this->instance);
        }

        throw new RuntimeException("Method '{$name}' is not defined! Did you mean 'is' or 'match'?");
    }

    /**
     * @return DecoderInterface<static>
     */
    final public static function type(): DecoderInterface
    {
        $mapped = [];
        $offset = 0;

        $original = self::union();

        foreach ($original->decoders as $key => $decoder) {
            $mapped[$key] = $decoder->map(fn($i) => new static($i, $offset));
            $offset++;
        }

        return $original instanceof TaggedUnionDecoder
            ? new TaggedUnionDecoder($original->tag, $mapped)
            : new UnionDecoder($mapped);
    }
}
