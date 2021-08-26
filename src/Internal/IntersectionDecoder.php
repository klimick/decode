<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalids;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T of array
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class IntersectionDecoder extends AbstractDecoder
{
    /** @var non-empty-list<AbstractDecoder<T>> $decoders */
    public array $decoders;

    /**
     * @param AbstractDecoder<T> $first
     * @param AbstractDecoder<T> $second
     * @param AbstractDecoder<T> ...$rest
     *
     * @no-named-arguments
     */
    public function __construct(AbstractDecoder $first, AbstractDecoder $second, AbstractDecoder ...$rest)
    {
        $this->decoders = [$first, $second, ...$rest];
    }

    public function name(): string
    {
        return implode(' & ', array_map(fn($t) => $t->name(), $this->decoders));
    }

    public function is(mixed $value): bool
    {
        foreach ($this->decoders as $decoder) {
            if (!$decoder->is($value)) {
                return false;
            }
        }

        return true;
    }

    public function decode(mixed $value, Context $context): Either
    {
        $decoded = [];
        $errors = [];

        foreach ($this->decoders as $decoder) {
            $result = $decoder
                ->decode($value, $context->append($decoder->name(), $value))
                ->get();

            if ($result instanceof Invalid) {
                $errors = [...$errors, ...$result->errors];
            } else {
                $decoded = array_merge($decoded, $result->value);
            }
        }

        /** @var T $decoded */

        return match (true) {
            !empty($errors) => invalids($errors),
            !empty($decoded) => valid($decoded),
        };
    }
}
