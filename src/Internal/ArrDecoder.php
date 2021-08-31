<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\Invalid;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;

/**
 * @template TKey of array-key
 * @template TVal
 * @extends AbstractDecoder<array<TKey, TVal>>
 * @psalm-immutable
 */
final class ArrDecoder extends AbstractDecoder
{
    /**
     * @param AbstractDecoder<TKey> $keyDecoder
     * @param AbstractDecoder<TVal> $valDecoder
     */
    public function __construct(
        public AbstractDecoder $keyDecoder,
        public AbstractDecoder $valDecoder,
    ) { }

    public function name(): string
    {
        return "array<{$this->keyDecoder->name()}, {$this->valDecoder->name()}>";
    }

    public function is(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        /**
         * @psalm-suppress TypeDoesNotContainType
         *     todo: !$this->keyDecoder->is($k)
         */
        foreach ($value as $k => $v) {
            if (!$this->keyDecoder->is($k) || !$this->valDecoder->is($v)) {
                return false;
            }
        }

        return true;
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
            $decodedK = $this->keyDecoder
                ->decode($k, $context($this->keyDecoder->name(), $k, (string) $k))
                ->get();

            $decodedV = $this->valDecoder
                ->decode($v, $context($this->valDecoder->name(), $k, (string) $k))
                ->get();

            if ($decodedV instanceof Invalid) {
                $errors = [...$errors, ...$decodedV->errors];
            }

            if ($decodedK instanceof Invalid) {
                $errors = [...$errors, ...$decodedK->errors];
            }

            if ($decodedK instanceof Valid && $decodedV instanceof Valid) {
                $decoded[$decodedK->value] = $decodedV->value;
            }
        }

        return 0 !== count($errors) ? invalids($errors) : valid($decoded);
    }
}
