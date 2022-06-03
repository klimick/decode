<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal\Shape;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\DecodeErrorInterface;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Internal\HighOrder\HighOrderDecoder;
use function Fp\Collection\map;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;
use function Klimick\Decode\Decoder\invalid;

/**
 * @template-covariant TVal
 * @extends AbstractDecoder<array<string, TVal>>
 * @psalm-immutable
 */
final class ShapeDecoder extends AbstractDecoder
{
    /**
     * @param array<string, DecoderInterface<TVal>> $decoders
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
            $decodedKV = ShapeAccessor::decodeProperty($context, $decoder, $key, $value);

            if ($decodedKV->isRight()) {
                $decoded[$key] = $decodedKV->get();
                continue;
            }

            if (self::isUndefinedAndOptional($decodedKV->get(), $decoder)) {
                continue;
            }

            $errors[] = $decodedKV->get();
        }

        return !empty($errors) ? invalids($errors) : valid($decoded);
    }

    /**
     * @param non-empty-list<DecodeErrorInterface> $errors
     * @psalm-pure
     */
    private static function isUndefinedAndOptional(array $errors, DecoderInterface $decoder): bool
    {
        return 1 === count($errors)
            && $errors[0] instanceof UndefinedError
            && $decoder instanceof HighOrderDecoder && $decoder->isOptional();
    }
}
