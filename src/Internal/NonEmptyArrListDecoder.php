<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use function Klimick\Decode\Decoder\arrList;
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

    public function is(mixed $value): bool
    {
        return arrList($this->decoder)->is($value) && 0 !== count($value);
    }

    public function decode(mixed $value, Context $context): Either
    {
        return arrList($this->decoder)
            ->decode($value, $context)
            ->flatMap(fn($valid) => 0 !== count($valid->value)
                ? valid($valid->value)
                : invalid($context));
    }
}
