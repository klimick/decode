<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\TaggedUnionDecoder;

/**
 * @psalm-immutable
 */
final class TaggedUnionDecoderFactory
{
    /**
     * @param non-empty-string $tag
     */
    public function __construct(private string $tag)
    {
    }

    /**
     * @template T
     *
     * @param DecoderInterface<T> ...$decoders
     * @return DecoderInterface<T>
     */
    public function __invoke(DecoderInterface ...$decoders): DecoderInterface
    {
        /**
         * Validated via psalm plugin hook at this moment
         * @var non-empty-array<non-empty-string, DecoderInterface> $decoders
         */
        return new TaggedUnionDecoder($this->tag, $decoders);
    }
}
