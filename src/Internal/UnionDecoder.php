<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use function Fp\Collection\map;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

/**
 * @template-covariant T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class UnionDecoder extends AbstractDecoder
{
    /**
     * @param non-empty-list<DecoderInterface<T>> $decoders
     */
    public function __construct(public array $decoders) { }

    public function name(): string
    {
        return implode(' | ', map($this->decoders, fn($d) => $d->name()));
    }

    public function decode(mixed $value, Context $context): Either
    {
        foreach ($this->decoders as $decoder) {
            $decoded = $decoder->decode($value, $context);

            if ($decoded->isRight()) {
                return valid($decoded->get());
            }
        }

        return invalid($context($this->name(), $value));
    }
}
