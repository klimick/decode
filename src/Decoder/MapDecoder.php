<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

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
        return "MapFrom<{$this->decoder->name()}>";
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder
            ->decode($value, $context)
            ->map($this->map);
    }
}
