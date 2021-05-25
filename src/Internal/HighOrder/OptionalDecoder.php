<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;

/**
 * @template T
 * @psalm-immutable
 */
final class OptionalDecoder extends AbstractDecoder
{
    /**
     * @param AbstractDecoder<T> $decoder
     */
    public function __construct(public AbstractDecoder $decoder) { }

    public function name(): string
    {
        return $this->decoder->name();
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder->decode($value, $context);
    }
}
