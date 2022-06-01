<?php

namespace Klimick\Decode\Decoder\Derive;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\ObjectDecoder;
use RuntimeException;

/**
 * @psalm-require-implements Props
 * @psalm-seal-properties
 */
trait Create
{
    /**
     * @param array<string, mixed> $data
     */
    private function __construct(private array $data)
    {
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if ($name === 'create') {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            return new self($arguments);
        }

        throw new RuntimeException("Method '{$name}' is not defined. Maybe you mean 'create'?");
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? throw new RuntimeException("Property with name '{$name}' is not defined!");
    }

    /**
     * @return DecoderInterface<static>
     */
    public static function type(): DecoderInterface
    {
        return new ObjectDecoder(static::class, self::props()->decoders);
    }
}
