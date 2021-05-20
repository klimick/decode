<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Fp\Functional\Either\Right;
use OutOfRangeException;
use RuntimeException;

/**
 * @psalm-immutable
 */
abstract class RuntimeData
{
    private array $properties;

    final public function __construct(mixed ...$properties)
    {
        $this->properties = $properties;
    }

    final public static function of(array $args): static
    {
        $decoded = decode(static::definition(), $args);

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

    /**
     * @return DecoderInterface<static>
     */
    abstract public static function definition(): DecoderInterface;
}
