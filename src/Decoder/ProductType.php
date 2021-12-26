<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use JsonSerializable;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

/**
 * @extends Runtype<ObjectDecoder<ProductType&static>>
 * @psalm-immutable
 * @psalm-seal-properties
 */
abstract class ProductType extends Runtype implements JsonSerializable
{
    private array $properties;

    final public function __construct(mixed ...$properties)
    {
        $decoder = static::type();

        $propertiesWithKeys = static::completeKeys($properties, $decoder);

        if (!$decoder->shape->is($propertiesWithKeys)) {
            throw new RuntimeException('Invalid data');
        }

        $this->properties = $propertiesWithKeys;
    }

    /**
     * Completes named arguments if instance was created with the new expression.
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

            if (array_key_exists($index, $decoderKeys)) {
                $withKeys[$decoderKeys[$index]] = $value;
            }
        }

        return $withKeys;
    }

    public function __get(string $name)
    {
        return $this->properties[$name] ?? null;
    }

    public function jsonSerialize(): array
    {
        return $this->properties;
    }

    /**
     * @psalm-return ObjectDecoder<static>
     */
    public static function type(): ObjectDecoder
    {
        $shapeDecoder = static::definition();

        $constructor = static function(array $properties): static {
            $classReflection = new ReflectionClass(static::class);

            /** @var static $instance */
            $instance = $classReflection->newInstanceWithoutConstructor();

            $propertiesReflection = new ReflectionProperty(ProductType::class, 'properties');
            $propertiesReflection->setAccessible(true);
            $propertiesReflection->setValue($instance, $properties);
            $propertiesReflection->setAccessible(false);

            return $instance;
        };

        return new ObjectDecoder(
            objectClass: static::class,
            decoders: $shapeDecoder->decoders,
            partial: $shapeDecoder->partial,
            customConstructor: $constructor,
        );
    }

    abstract protected static function definition(): ShapeDecoder;
}
