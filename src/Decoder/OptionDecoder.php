<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Context;

/**
 * @template-covariant T
 * @extends AbstractDecoder<Option<T>>
 * @psalm-immutable
 */
final class OptionDecoder extends AbstractDecoder
{
    /**
     * @param DecoderInterface<T> $decoder
     */
    public function __construct(
        private DecoderInterface $decoder,
    ) {}

    public function name(): string
    {
        return "Option<{$this->decoder->name()}>";
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->decoder->decode($value, $context)
            ->fold(
                fn($decoded) => Either::right(Option::some($decoded)),
                fn($errors) => null === $value
                    ? Either::right(Option::none())
                    : Either::left($errors),
            );
    }
}
