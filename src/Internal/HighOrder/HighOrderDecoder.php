<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\HighOrder\Brand\FromBrand;
use Klimick\Decode\HighOrder\Brand\ConstrainedBrand;
use Klimick\Decode\HighOrder\Brand\DefaultBrand;
use Klimick\Decode\HighOrder\Brand\OptionalBrand;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
abstract class HighOrderDecoder extends AbstractDecoder
    implements OptionalBrand, ConstrainedBrand, FromBrand, DefaultBrand
{
    /**
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(
        public DecoderInterface $decoder,
    ) { }

    public function name(): string
    {
        return $this->decoder->name();
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder->decode($value, $context);
    }

    /**
     * @internal
     * @psalm-assert-if-true DefaultDecoder $this->asDefault()
     */
    public function isDefault(): bool
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->isDefault()
            : false;
    }

    /**
     * @internal
     */
    public function asDefault(): ?DefaultDecoder
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->asDefault()
            : null;
    }

    /**
     * @internal
     * @psalm-assert-if-true OptionalDecoder $this->asOptional()
     */
    public function isOptional(): bool
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->isOptional()
            : false;
    }

    /**
     * @internal
     */
    public function asOptional(): ?OptionalDecoder
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->asOptional()
            : null;
    }

    /**
     * @internal
     * @psalm-assert-if-true FromDecoder $this->asFrom()
     */
    public function isFrom(): bool
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->isFrom()
            : false;
    }

    /**
     * @internal
     */
    public function asFrom(): ?FromDecoder
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->asFrom()
            : null;
    }

    /**
     * @internal
     * @psalm-assert-if-true ConstrainedDecoder $this->asConstrained()
     */
    public function isConstrained(): bool
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->isConstrained()
            : false;
    }

    /**
     * @internal
     */
    public function asConstrained(): ?ConstrainedDecoder
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->asConstrained()
            : null;
    }
}
