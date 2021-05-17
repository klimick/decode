<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\HighOrder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;

/**
 * @template T
 * @implements DecoderInterface<T>
 * @psalm-immutable
 */
final class AliasedDecoder implements DecoderInterface
{
    /**
     * @param non-empty-string $alias
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(
        public string $alias,
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
}
