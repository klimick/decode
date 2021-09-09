<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\PsalmDecode\ObjectDecoder\ObjectDecoderFactoryReturnTypeProvider;

/**
 * @template TObjectClass of object
 * @template TPartial of bool
 * @psalm-immutable
 */
final class ObjectDecoderFactory
{
    /**
     * @param class-string<TObjectClass> $objectClass
     * @param TPartial $partial,
     */
    public function __construct(
        public string $objectClass,
        public bool $partial = false,
    ) { }

    /**
     * @psalm-param AbstractDecoder ...$decoders
     * @return AbstractDecoder<TObjectClass>
     *
     * @see ObjectDecoderFactoryReturnTypeProvider
     */
    public function __invoke(AbstractDecoder ...$decoders): AbstractDecoder
    {
        /**
         * Validated via psalm plugin hook at this moment
         * @psalm-var array<string, AbstractDecoder<mixed>> $decoders
         */
        return new ObjectDecoder($this->objectClass, $decoders, $this->partial);
    }
}
