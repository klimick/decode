<?php

namespace Klimick\Decode\Decoder\Derive;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
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
        if (!self::props()->is($this)) {
            throw new RuntimeException('Given props is invalid');
        }
    }

    public function __call(string $name, array $arguments)
    {
        if ($name === 'create') {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            return new self($arguments);
        }

        throw new \RuntimeException('not implemented');
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? throw new RuntimeException("No prop with name '{$name}'");
    }

    /**
     * @return DecoderInterface<static>
     */
    public static function type(): DecoderInterface
    {
        /** @var ShapeDecoder $shape */
        $shape = self::props();

        /** @var DecoderInterface<static> */
        return new ObjectDecoder(static::class, $shape->decoders);
    }
}
