<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Collections\Map;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\Invalid;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Decoder\nonEmptyArrList;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<list<T>>
 * @psalm-immutable
 */
final class TupleDecoder extends AbstractDecoder
{
    /**
     * @param Map<int, AbstractDecoder<T>> $decoders
     */
    public function __construct(private Map $decoders) { }

    public function name(): string
    {
        $types = $this->decoders
            ->map(fn($kv) => $kv->value->name())
            ->values()
            ->toArray();

        return 'array{' . implode(', ', $types) . '}';
    }

    public function is(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return $this->decoders->every(
            fn($decoder, $index) => array_key_exists($index, $value) && $decoder->is($value[$index]),
        );
    }

    public function decode(mixed $value, Context $context): Either
    {
        return nonEmptyArrList(mixed())
            ->decode($value, $context)
            ->map(fn($valid) => $valid->value)
            ->flatMap(function($tuple) use ($context) {
                if (count($tuple) !== count($this->decoders)) {
                    return invalid($context);
                }

                $decoded = [];
                $errors = [];

                foreach ($this->decoders->toArray() as [$k, $decoder]) {
                    $result = $decoder
                        ->decode($tuple[$k], $context($decoder->name(), $tuple[$k], (string) $k))
                        ->get();

                    if ($result instanceof Invalid) {
                        $errors = [...$errors, ...$result->errors];
                    } else {
                        $decoded[] = $result->value;
                    }
                }

                return 0 !== count($errors) ? invalids($errors) : valid($decoded);
            });
    }
}
