<?php

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\ObjectDecoder;

/**
 * @psalm-require-implements InferShape
 * @psalm-seal-properties
 */
trait ObjectInstance
{
    private array $properties;

    /**
     * @internal
     */
    public final function __construct(mixed ...$data)
    {
        $this->properties = $data;
    }

    public function __get(string $name): mixed
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * @return DecoderInterface<static>
     */
    public static function type(): DecoderInterface
    {
        return new ObjectDecoder(static::class, self::shape()->decoders);
    }
}
