<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder;

/**
 * @template T
 * @extends Decoder<T>
 * @psalm-immutable
 */
final class AliasedDecoder extends Decoder
{
    /**
     * @param non-empty-string $alias
     * @param Decoder<T> $decoder
     */
    public function __construct(
        public string $alias,
        public Decoder $decoder,
    ) { }

    public function name(): string
    {
        return $this->decoder->name();
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder->decode($value, $context);
    }
}
