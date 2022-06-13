<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;
use function Fp\Collection\filter;
use function Fp\Collection\map;
use function in_array;
use function is_int;

/**
 * @template-covariant TShape of array
 * @extends AbstractDecoder<TShape>
 * @psalm-immutable
 */
final class ShapeDecoder extends AbstractDecoder
{
    /**
     * @param array<int|string, DecoderInterface<mixed>> $decoders
     */
    public function __construct(
        public array $decoders,
    ) { }

    public function name(): string
    {
        $properties = implode(', ', map($this->decoders, function(DecoderInterface $decoder, int|string $property) {
            if (is_int($property)) {
                return $decoder->name();
            }

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
        return new self(filter($this->decoders, fn($_, $prop) => !in_array($prop, $props), preserveKeys: true));
    }

    /**
     * @param non-empty-list<string> $props
     */
    public function pick(array $props): self
    {
        return new self(filter($this->decoders, fn($_, $prop) => in_array($prop, $props), preserveKeys: true));
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

            if (ShapeAccessor::isUndefined($decodedKV->get()) && $decoder->isPossiblyUndefined()) {
                continue;
            }

            $errors[] = $decodedKV->get();
        }

        /** @var TShape $decoded */;

        return !empty($errors) ? invalids($errors) : valid($decoded);
    }
}
