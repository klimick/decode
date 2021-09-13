<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\DecoderInterface;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends HighOrderDecoder<T>
 * @psalm-immutable
 */
final class DefaultDecoder extends HighOrderDecoder
{
    /**
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(public mixed $default, DecoderInterface $decoder)
    {
        parent::__construct($decoder);
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function asDefault(): ?DefaultDecoder
    {
        return $this;
    }

    public function name(): string
    {
        return $this->decoder->name();
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder
            ->decode($value, $context)
            ->orElse(fn() => valid($this->default));
    }
}
