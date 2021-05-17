<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Fp\Functional\Either\Right;
use Klimick\Decode\Context;
use Klimick\Decode\Invalid;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\invalids;

/**
 * @template T
 * @implements DecoderInterface<T>
 * @psalm-immutable
 */
final class UnionDecoder implements DecoderInterface
{
    /** @var non-empty-list<DecoderInterface<T>> $decoders */
    public array $decoders;

    /**
     * @param DecoderInterface<T> $first
     * @param DecoderInterface<T> $second
     * @param DecoderInterface<T> ...$rest
     *
     * @no-named-arguments
     */
    public function __construct(DecoderInterface $first, DecoderInterface $second, DecoderInterface ...$rest)
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
