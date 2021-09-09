<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\UnionCaseErrors;
use Klimick\Decode\Decoder\UnionTypeErrors;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Fp\Collection\exists;
use function Klimick\Decode\Decoder\valid;
use function Klimick\Decode\Decoder\invalids;

/**
 * @template-covariant T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class UnionDecoder extends AbstractDecoder
{
    /**
     * @param non-empty-list<AbstractDecoder<T>> $decoders
     */
    public function __construct(private array $decoders) { }

    public function name(): string
    {
        return implode(' | ', array_map(fn($d) => $d->name(), $this->decoders));
    }

    public function is(mixed $value): bool
    {
        return exists($this->decoders, fn(AbstractDecoder $d) => $d->is($value));
    }

    public function decode(mixed $value, Context $context): Either
    {
        $errors = [];

        foreach ($this->decoders as $decoder) {
            $typename = $decoder->name();

            $decoded = $decoder
                ->decode($value, $context($typename, $value))
                ->get();

            if ($decoded instanceof Valid) {
                return valid($decoded->value);
            }

            $errors[] = new UnionCaseErrors($typename, $decoded->errors);
        }

        return invalids([
            new UnionTypeErrors($errors),
        ]);
    }
}
