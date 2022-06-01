<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use function Fp\Collection\map;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;
use function Klimick\Decode\Decoder\invalid;

/**
 * @template-covariant TShape of array
 * @extends AbstractDecoder<TShape>
 * @psalm-immutable
 */
final class ShapeDecoder extends AbstractDecoder
{
    /**
     * @param array<string, DecoderInterface> $decoders
     */
    public function __construct(
        public array $decoders,
        public bool $partial = false,
    ) { }

    public function name(): string
    {
        $properties = implode(', ', map($this->decoders, function(DecoderInterface $decoder, string $property) {
            if ($decoder instanceof HighOrderDecoder && $decoder->isOptional()) {
                return "{$property}?: {$decoder->name()}";
            }

            return "{$property}: {$decoder->name()}";
        }));

        return "array{{$properties}}";
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $decoded = [];
        $errors = [];

        foreach ($this->decoders as $key => $decoder) {
            $decodedKV = ShapeAccessor::decodeProperty($context, $decoder, $key, $value)->get();

            if ($decodedKV instanceof Valid) {
                /** @psalm-suppress MixedAssignment */
                $decoded[$key] = $decodedKV->value;
            } elseif ($decodedKV->isUndefined() && $decoder instanceof HighOrderDecoder && $decoder->isOptional()) {
                continue;
            } else {
                $errors = [...$errors, ...$decodedKV->errors];
            }
        }

        /** @var Either<Invalid, Valid<TShape>> */
        return !empty($errors) ? invalids($errors) : valid($decoded);
    }
}
