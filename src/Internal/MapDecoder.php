<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Closure;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\Valid;

/**
 * @template TDecoded
 * @template TMapped
 * @extends AbstractDecoder<TMapped>
 * @psalm-immutable
 */
final class MapDecoder extends AbstractDecoder
{
    /**
     * @param DecoderInterface<TDecoded> $decoder
     * @param Closure(TDecoded): TMapped $map
     */
    public function __construct(
        public DecoderInterface $decoder,
        public Closure $map,
    ) {}

    public function name(): string
    {
        return 'unknown-mapped-type';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder->decode($value, $context)->map(
            fn($valid) => new Valid(($this->map)($valid->value)),
        );
    }

    public function is(mixed $value): bool
    {
        return true; // no way to prove type
    }
}
