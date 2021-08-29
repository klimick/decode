<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\UnionCaseErrors;
use Klimick\Decode\Decoder\UnionTypeErrors;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\valid;
use function Klimick\Decode\Decoder\invalids;

/**
 * @template-covariant T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class UnionDecoder extends AbstractDecoder
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
        return implode(' | ', array_map(fn($t) => $t->name(), $this->decoders));
    }

    public function is(mixed $value): bool
    {
        foreach ($this->decoders as $decoder) {
            if ($decoder->is($value)) {
                return true;
            }
        }

        return false;
    }

    public function decode(mixed $value, Context $context): Either
    {
        $errors = [];

        foreach ($this->decoders as $decoder) {
            $typename = $decoder->name();

            $result = $decoder
                ->decode($value, $context->append($typename, $value))
                ->get();

            if ($result instanceof Valid) {
                return valid($result->value);
            }

            $errors[] = new UnionCaseErrors($typename, $result->errors);
        }

        return invalids([
            new UnionTypeErrors($errors),
        ]);
    }
}
