<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\HighOrder\Brand\AliasedBrand;
use Klimick\Decode\HighOrder\Brand\ConstrainedBrand;
use Klimick\Decode\HighOrder\Brand\DefaultBrand;
use Klimick\Decode\HighOrder\Brand\FromSelfBrand;
use Klimick\Decode\HighOrder\Brand\OptionalBrand;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
abstract class HighOrderDecoder extends AbstractDecoder
    implements OptionalBrand, ConstrainedBrand, FromSelfBrand, AliasedBrand, DefaultBrand
{
    /**
     * @param AbstractDecoder<T> $decoder
     */
    public function __construct(
        public AbstractDecoder $decoder,
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
     * @psalm-assert-if-true AliasedDecoder $this->asAliased()
     */
    public function isAliased(): bool
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->isAliased()
            : false;
    }

    /**
     * @internal
     */
    public function asAliased(): ?AliasedDecoder
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->asAliased()
            : null;
    }

    /**
     * @internal
     * @psalm-assert-if-true FromSelfDecoder $this->asFromSelf()
     */
    public function isFromSelf(): bool
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->isFromSelf()
            : false;
    }

    /**
     * @internal
     */
    public function asFromSelf(): ?FromSelfDecoder
    {
        return $this->decoder instanceof HighOrderDecoder
            ? $this->decoder->asFromSelf()
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
