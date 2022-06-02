<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use function Klimick\Decode\Decoder\listOf;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<non-empty-list<T>>
 * @psalm-immutable
 */
final class NonEmptyArrListDecoder extends AbstractDecoder
{
    /**
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(public DecoderInterface $decoder) { }

    public function name(): string
    {
        return "non-empty-list<{$this->decoder->name()}>";
    }

    public function decode(mixed $value, Context $context): Either
    {
        return listOf($this->decoder)
            ->decode($value, $context)
            ->flatMap(fn($valid) => 0 !== count($valid)
                ? valid($valid)
                : invalid($context));
    }
}
