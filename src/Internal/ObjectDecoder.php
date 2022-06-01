<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use function Fp\Collection\every;
use function Klimick\Decode\Decoder\valid;

/**
 * @template-covariant T of object
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class ObjectDecoder extends AbstractDecoder
{
    public ShapeDecoder $shape;

    /**
     * @param class-string<T> $objectClass
     * @param array<string, DecoderInterface<mixed>> $decoders
     */
    public function __construct(
        public string $objectClass,
        public array $decoders,
        public bool $partial = false,
    ) {
        $this->shape = new ShapeDecoder($decoders, $partial);
    }

    public function name(): string
    {
        return $this->objectClass;
    }

    public function is(mixed $value): bool
    {
        return $value instanceof $this->objectClass &&
            every($this->decoders, fn(DecoderInterface $decoder, string $key) => $decoder->is($value->{$key}));
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->shape
            ->decode($value, $context)
            ->flatMap(function($validShape) {
                /** @psalm-suppress MixedMethodCall */
                $instance = new ($this->objectClass)(...$validShape->value);

                return valid($instance);
            });
    }
}
