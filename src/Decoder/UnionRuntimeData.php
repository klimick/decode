<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\UnionDecoder;
use RuntimeException;

/**
 * @psalm-immutable
 */
abstract class UnionRuntimeData
{
    final public function __construct(
        private string $type,
        private RuntimeData|UnionRuntimeData $instance,
    ) {}

    final public static function of(array $args): static
    {
        return decode($args, static::type())
            ->map(fn(Valid $v) => $v->value)
            ->mapLeft(fn() => throw new RuntimeException('Could not create RuntimeData. Check psalm issues.'))
            ->get();
    }

    final public function match(callable ...$matchers): mixed
    {
        return ($matchers[$this->type])($this->instance);
    }

    /**
     * @psalm-return AbstractDecoder<static> & UnionDecoder<static>
     */
    final public static function type(): AbstractDecoder
    {
        $cases = static::cases();

        return union(...array_map(
            fn($decoder, $type) => object(static::class)(
                type: fallback($type),
                instance: $decoder->from('$'),
            ),
            array_values($cases),
            array_keys($cases),
        ));
    }

    /**
     * @psalm-return non-empty-array<non-empty-string, ObjectDecoder<RuntimeData> | UnionDecoder<UnionRuntimeData>>
     */
    abstract protected static function cases(): array;
}
