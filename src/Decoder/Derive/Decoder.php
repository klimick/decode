<?php

namespace Klimick\Decode\Decoder\Derive;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\ObjectDecoder;

/**
 * @psalm-require-implements Props
 * @psalm-seal-properties
 */
trait Decoder
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
        return new ObjectDecoder(static::class, self::props()->decoders);
    }
}
