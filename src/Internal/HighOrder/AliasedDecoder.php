<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;

/**
 * @template T
 * @extends HighOrderDecoder<T>
 * @psalm-immutable
 */
final class AliasedDecoder extends HighOrderDecoder
{
    /**
     * @param non-empty-string $alias
     * @param AbstractDecoder<T> $decoder
     */
    public function __construct(public string $alias, AbstractDecoder $decoder)
    {
        parent::__construct($decoder);
    }

    public function isAliased(): bool
    {
        return true;
    }

    public function asAliased(): ?AliasedDecoder
    {
        return $this;
    }

    public function name(): string
    {
        return $this->decoder->name();
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder->decode($value, $context);
    }
}
