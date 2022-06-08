<?php

namespace Klimick\Decode\Decoder;

/**
 * @psalm-require-implements InferShape
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
        /** @var array<string, DecoderInterface<mixed>> $decoders */
        $decoders = self::shape()->decoders;

        return new ObjectDecoder(static::class, $decoders);
    }
}
