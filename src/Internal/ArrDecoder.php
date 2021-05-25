<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Left;
use Fp\Functional\Either\Right;
use Klimick\Decode\Context;
use Klimick\Decode\AbstractDecoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\invalids;
use function Klimick\Decode\valid;

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

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_array($value)) {
            return invalid($context);
        }

        $decoded = [];
        $errors = [];

        /** @var mixed $v */
        foreach ($value as $k => $v) {
            $decodedK = $this->keyDecoder->decode($k, $context->append($this->keyDecoder->name(), $k, (string) $k));
            $decodedV = $this->valDecoder->decode($v, $context->append($this->valDecoder->name(), $k, (string) $k));

            if ($decodedK instanceof Right && $decodedV instanceof Right) {
                $val = $decodedV->get();
                $key = $decodedK->get();

                $decoded[$key->value] = $val->value;
            }

            if ($decodedV instanceof Left) {
                $errors = [...$errors, ...$decodedV->get()->errors];
            }

            if ($decodedK instanceof Left) {
                $errors = [...$errors, ...$decodedK->get()->errors];
            }
        }

        return empty($errors) ? valid($decoded) : invalids($errors);
    }
}
