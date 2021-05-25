<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Right;
use JsonSerializable;
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
        $decoded = decode($args, static::definition());

        if ($decoded instanceof Right) {
            return $decoded->get()->value;
        }

        throw new RuntimeException('Could not create RuntimeData. Check psalm issues.');
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

    abstract public static function definition(): AbstractDecoder;
}