<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use JsonException;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use function Klimick\Decode\Decoder\invalid;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class FromJsonDecoder extends AbstractDecoder
{
    /**
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(
        public DecoderInterface $decoder,
    ) { }

    public function name(): string
    {
        return $this->decoder->name();
    }

    public function is(mixed $value): bool
    {
        return $this->decoder->is($value);
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_string($value)) {
            return invalid($context);
        }

        try {
            /** @psalm-var mixed $decoded */
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return invalid($context);
        }

        return $this->decoder->decode($decoded, $context);
    }
}
