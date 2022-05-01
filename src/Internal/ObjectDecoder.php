<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Closure;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
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
     * @param null|Closure(array): T $customConstructor
     */
    public function __construct(
        public string $objectClass,
        public array $decoders,
        public bool $partial = false,
        public null|Closure $customConstructor = null,
    ) {
        $this->shape = new ShapeDecoder($decoders, $partial);
    }

    public function name(): string
    {
        return $this->objectClass;
    }

    public function is(mixed $value): bool
    {
        if (!($value instanceof $this->objectClass)) {
            return false;
        }

        foreach ($this->decoders as $key => $decoder) {
            if (!$decoder->is($value->{$key})) {
                return false;
            }
        }

        return $value instanceof $this->objectClass;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->shape
            ->decode($value, $context)
            ->flatMap(function($validShape) {
                /** @psalm-suppress MixedMethodCall */
                $instance = null !== $this->customConstructor
                    ? ($this->customConstructor)($validShape->value)
                    : new ($this->objectClass)(...$validShape->value);

                return valid($instance);
            });
    }
}
