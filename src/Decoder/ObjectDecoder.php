<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;

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
    public function __construct(public string $objectClass, public array $decoders)
    {
        $this->shape = new ShapeDecoder($decoders);
    }

    public function name(): string
    {
        return $this->objectClass;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->shape
            ->decode($value, $context)
            ->flatMap(function($validShape) {
                /** @psalm-suppress MixedMethodCall */
                $instance = new ($this->objectClass)(...$validShape);

                return valid($instance);
            });
    }
}
