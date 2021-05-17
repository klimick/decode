<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Valid;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use function Klimick\Decode\valid;

/**
 * @template T of object
 * @implements DecoderInterface<T>
 * @psalm-immutable
 */
final class ObjectDecoder implements DecoderInterface
{
    private ShapeDecoder $decoder;

    /**
     * @param class-string<T> $objectClass
     * @param array<array-key, DecoderInterface<mixed>> $decoders
     */
    public function __construct(
        public string $objectClass,
        public array $decoders,
        public bool $partial = false,
    )
    {
        $this->decoder = new ShapeDecoder($decoders, $partial);
    }

    public function name(): string
    {
        return $this->objectClass;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder
            ->decode($value, $context)
            ->flatMap(function(Valid $valid) {
                /** @psalm-suppress MixedMethodCall */
                $instance = new ($this->objectClass)(...$valid->value);

                return valid($instance);
            });
    }
}
