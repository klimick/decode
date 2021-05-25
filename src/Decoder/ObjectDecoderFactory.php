<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\ToDecoder;
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
     * @psalm-param AbstractDecoder|pure-callable(): AbstractDecoder ...$decoders
     * @return AbstractDecoder<TObjectClass>
     *
     * @see ObjectDecoderFactoryReturnTypeProvider
     */
    public function __invoke(callable|AbstractDecoder ...$decoders): AbstractDecoder
    {
        return new ObjectDecoder($this->objectClass, ToDecoder::forAll($decoders), $this->partial);
    }
}