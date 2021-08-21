<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use JsonSerializable;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use OutOfRangeException;
use RuntimeException;

/**
 * @psalm-immutable
 */
abstract class RuntimeData implements JsonSerializable
{
    private array $properties;

    final public function __construct(mixed ...$properties)
    {
        $this->properties = $properties;
    }

    final public static function of(array $args): static
    {
        return decode($args, static::type())
            ->map(fn(Valid $v) => $v->value)
            ->mapLeft(fn() => throw new RuntimeException('Could not create RuntimeData. Check psalm issues.'))
            ->get();
    }

    public function __get(string $name)
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new OutOfRangeException("Property '{$name}' is undefined. Check psalm issues.");
        }

        return $this->properties[$name];
    }

    public function jsonSerialize(): array
    {
        return $this->properties;
    }

    public static function type(): AbstractDecoder
    {
        $shapeDecoder = static::properties();
        assert($shapeDecoder instanceof ShapeDecoder, 'Must be type checked via psalm plugin');

        return new ObjectDecoder(
            objectClass: static::class,
            decoders: $shapeDecoder->decoders,
            partial: $shapeDecoder->partial,
        );
    }

    abstract protected static function properties(): AbstractDecoder;
}
