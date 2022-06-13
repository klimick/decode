<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class WithNameDecoder extends AbstractDecoder
{
    /**
     * @param non-empty-string $name
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(
        public string $name,
        public DecoderInterface $decoder,
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder->decode($value, $context);
    }
}
