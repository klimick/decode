<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

/**
 * @template TKey of array-key
 * @template TVal
 * @extends AbstractDecoder<array<TKey, TVal>>
 * @psalm-immutable
 */
final class ArrDecoder extends AbstractDecoder
{
    /**
     * @param DecoderInterface<TKey> $keyDecoder
     * @param DecoderInterface<TVal> $valDecoder
     */
    public function __construct(
        public DecoderInterface $keyDecoder,
        public DecoderInterface $valDecoder,
    ) { }

    public function name(): string
    {
        return "array<{$this->keyDecoder->name()}, {$this->valDecoder->name()}>";
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $decoded = [];
        $errors = [];

        /** @psalm-suppress MixedAssignment */
        foreach ($value as $k => $v) {
            $decodedK = $this->keyDecoder->decode($k, $context($this->keyDecoder->name(), $k, (string) $k));
            $decodedV = $this->valDecoder->decode($v, $context($this->valDecoder->name(), $v, (string) $k));

            if ($decodedV->isLeft()) {
                $errors[] = $decodedV->get();
            }

            if ($decodedK->isLeft()) {
                $errors[] = $decodedK->get();
            }

            if ($decodedK->isRight() && $decodedV->isRight()) {
                $decoded[$decodedK->get()] = $decodedV->get();
            }
        }

        return 0 !== count($errors) ? invalids($errors) : valid($decoded);
    }
}
