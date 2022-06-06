<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder\HighOrder;

use Klimick\Decode\Decoder\DecoderInterface;

/**
 * @template T
 * @extends HighOrderDecoder<T>
 * @psalm-immutable
 */
final class OptionalDecoder extends HighOrderDecoder
{
    /**
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(DecoderInterface $decoder)
    {
        parent::__construct($decoder);
    }

    public function isOptional(): bool
    {
        return true;
    }

    public function asOptional(): ?OptionalDecoder
    {
        return $this;
    }
}
