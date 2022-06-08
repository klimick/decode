<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Error\DecodeErrorInterface;
use Klimick\Decode\Decoder\Error\UndefinedError;
use function Fp\Collection\filter;
use function Fp\Collection\map;
use function in_array;

/**
 * @template TShape of array
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
            return $decoder->isPossiblyUndefined()
                ? "{$property}?: {$decoder->name()}"
                : "{$property}: {$decoder->name()}";
        }));

        return "array{{$properties}}";
    }

    /**
     * @param non-empty-list<string> $props
     */
    public function omit(array $props): self
    {
        return new self(
            decoders: filter($this->decoders, fn($_, $prop) => !in_array($prop, $props), preserveKeys: true),
            partial: $this->partial,
        );
    }

    /**
     * @param non-empty-list<string> $props
     */
    public function pick(array $props): self
    {
        return new self(
            decoders: filter($this->decoders, fn($_, $prop) => in_array($prop, $props), preserveKeys: true),
            partial: $this->partial,
        );
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
                /** @psalm-suppress MixedAssignment */
                $decoded[$key] = $decodedKV->get();
                continue;
            }

            if (self::isUndefined($decodedKV->get())) {
                if ($decoder->isPossiblyUndefined()) {
                    continue;
                }

                if ($decoder instanceof OptionDecoder) {
                    $decoded[$key] = Option::none();
                    continue;
                }
            }

            $errors[] = $decodedKV->get();
        }

        /** @var TShape $decoded */;

        return !empty($errors) ? invalids($errors) : valid($decoded);
    }

    /**
     * @param non-empty-list<DecodeErrorInterface> $errors
     * @psalm-pure
     */
    private static function isUndefined(array $errors): bool
    {
        return 1 === count($errors) && $errors[0] instanceof UndefinedError;
    }
}
