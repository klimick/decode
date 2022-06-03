<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use function Fp\Collection\map;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;

/**
 * @extends AbstractDecoder<array>
 * @psalm-immutable
 */
final class IntersectionDecoder extends AbstractDecoder
{
    /**
     * @param non-empty-list<DecoderInterface<array>> $decoders
     */
    public function __construct(public array $decoders)
    {
    }

    public function name(): string
    {
        $properties = implode(' & ', map(
            $this->decoders,
            fn(DecoderInterface $decoder) => $decoder->name(),
        ));

        return "array{{$properties}}";
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $merged = [];
        $errors = [];

        foreach ($this->decoders as $decoder) {
            $decoded = $decoder->decode($value, $context($decoder->name(), $value));

            if ($decoded->isRight()) {
                $merged = array_merge($merged, $decoded->get());
            } else {
                $errors[] = $decoded->get();
            }
        }

        return !empty($errors) ? invalids($errors) : valid($merged);
    }
}
