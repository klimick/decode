<?php

declare(strict_types=1);

namespace Klimick\Decode;

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
     * @psalm-param DecoderInterface<mixed>|pure-callable(): DecoderInterface<mixed> ...$decoders
     * @return DecoderInterface<TObjectClass>
     *
     * @see ObjectDecoderFactoryReturnTypeProvider
     */
    public function __invoke(callable|DecoderInterface ...$decoders): DecoderInterface
    {
        return new ObjectDecoder($this->objectClass, ToDecoder::forAll($decoders), $this->partial);
    }
}
