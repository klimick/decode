<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use JsonSerializable;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use OutOfRangeException;

/**
 * @psalm-immutable
 */
abstract class RuntimeData implements JsonSerializable
{
    private array $properties;

    final public function __construct(mixed ...$properties)
    {
        $this->properties = static::completeKeys($properties, static::type());
    }

    /**
     * Completes named arguments if instance was created with the new expression.
     * If instance was created with the decoding this call is just redundant.
     *
     * @psalm-suppress MixedAssignment
     */
    private static function completeKeys(array $values, ObjectDecoder $decoder): array
    {
        $decoders = $decoder->shape->decoders;

        $decoderKeys = array_keys($decoders);
        $withKeys = [];

        foreach ($values as $index => $value) {
            if (array_key_exists($index, $decoders)) {
                $withKeys[$index] = $value;
                continue;
            }

            if (!array_key_exists($index, $decoderKeys)) {
                continue;
            }

            $withKeys[$decoderKeys[$index]] = $value;
        }

        return $withKeys;
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

    /**
     * @psalm-return AbstractDecoder<static> & ObjectDecoder<static>
     */
    public static function type(): AbstractDecoder
    {
        /** @var null|(AbstractDecoder<static> & ObjectDecoder<static>) $decoder */
        static $decoder = null;

        if (null !== $decoder) {
            return $decoder;
        }

        $shapeDecoder = static::properties();

        return ($decoder = new ObjectDecoder(
            objectClass: static::class,
            decoders: $shapeDecoder->decoders,
            partial: $shapeDecoder->partial,
        ));
    }

    abstract protected static function properties(): ShapeDecoder;
}
