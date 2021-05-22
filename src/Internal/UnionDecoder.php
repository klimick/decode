<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Right;
use Klimick\Decode\Context;
use Klimick\Decode\Invalid;
use Klimick\Decode\Decoder;
use function Klimick\Decode\invalids;

/**
 * @template T
 * @extends Decoder<T>
 * @psalm-immutable
 */
final class UnionDecoder extends Decoder
{
    /** @var non-empty-list<Decoder<T>> $decoders */
    public array $decoders;

    /**
     * @param Decoder<T> $first
     * @param Decoder<T> $second
     * @param Decoder<T> ...$rest
     *
     * @no-named-arguments
     */
    public function __construct(Decoder $first, Decoder $second, Decoder ...$rest)
    {
        $this->decoders = [$first, $second, ...$rest];
    }

    public function name(): string
    {
        return implode(' | ', array_map(fn($t) => $t->name(), $this->decoders));
    }

    public function decode(mixed $value, Context $context): Either
    {
        $errors = [];

        foreach ($this->decoders as $decoder) {
            $result = $decoder->decode($value, $context->append($decoder->name(), $value));

            if ($result instanceof Right) {
                return $result;
            }

            /** @var Invalid $invalid */
            $invalid = $result->get();

            $errors = [...$errors, ...$invalid->errors];
        }

        return invalids($errors);
    }
}
