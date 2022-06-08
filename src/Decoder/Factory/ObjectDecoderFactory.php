<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\Factory;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\ObjectDecoder;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ObjectDecoderFactoryReturnTypeProvider;

/**
 * @template TObjectClass of object
 * @psalm-immutable
 */
final class ObjectDecoderFactory
{
    /**
     * @param class-string<TObjectClass> $objectClass
     */
    public function __construct(
        public string $objectClass,
    ) {}

    /**
     * @return DecoderInterface<TObjectClass>
     * @see ObjectDecoderFactoryReturnTypeProvider
     */
    public function __invoke(DecoderInterface ...$decoders): DecoderInterface
    {
        /**
         * Validated via psalm plugin hook at this moment
         * @psalm-var array<string, DecoderInterface<mixed>> $decoders
         */
        return new ObjectDecoder($this->objectClass, $decoders);
    }
}
