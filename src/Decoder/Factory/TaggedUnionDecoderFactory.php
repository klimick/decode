<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\Factory;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\TaggedUnionDecoder;

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
     * @return TaggedUnionDecoder<T>
     */
    public function __invoke(DecoderInterface ...$decoders): TaggedUnionDecoder
    {
        /**
         * Validated via psalm plugin hook at this moment
         * @var non-empty-array<non-empty-string, DecoderInterface> $decoders
         */
        return new TaggedUnionDecoder($this->tag, $decoders);
    }
}
