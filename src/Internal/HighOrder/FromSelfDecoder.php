<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Klimick\Decode\Decoder\AbstractDecoder;

/**
 * @template T
 * @extends HighOrderDecoder<T>
 * @psalm-immutable
 */
final class FromSelfDecoder extends HighOrderDecoder
{
    /**
     * @param AbstractDecoder<T> $decoder
     */
    public function __construct(AbstractDecoder $decoder)
    {
        parent::__construct($decoder);
    }

    public function isFromSelf(): bool
    {
        return true;
    }

    public function asFromSelf(): ?FromSelfDecoder
    {
        return $this;
    }
}
