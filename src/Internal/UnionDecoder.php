<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Collections\NonEmptyArrayList;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Invalid;
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
    /**
     * @param NonEmptyArrayList<AbstractDecoder<T>> $decoders
     */
    public function __construct(private NonEmptyArrayList $decoders) { }

    public function name(): string
    {
        $types = $this->decoders
            ->map(fn($t) => $t->name())
            ->toArray();

        return implode(' | ', $types);
    }

    public function is(mixed $value): bool
    {
        return $this->decoders->exists(fn($decoder) => $decoder->is($value));
    }

    public function decode(mixed $value, Context $context): Either
    {
        return self::decodeRec(
            toDecode: $value,
            inContext: $context,
            decoder: $this->decoders->head(),
            decoders: $this->decoders->tail()->toArray(),
        );
    }

    /**
     * @template TUnion
     *
     * @param AbstractDecoder<TUnion> $decoder
     * @param list<AbstractDecoder<TUnion>> $decoders
     * @param list<UnionCaseErrors> $errors
     * @return Either<Invalid, Valid<TUnion>>
     *
     * @psalm-pure
     */
    private static function decodeRec(
        mixed $toDecode,
        Context $inContext,
        AbstractDecoder $decoder,
        array $decoders,
        array $errors = [],
    ): Either
    {
        $typename = $decoder->name();

        $decoded = $decoder
            ->decode($toDecode, $inContext($typename, $toDecode))
            ->get();

        if ($decoded instanceof Valid) {
            return valid($decoded->value);
        }

        $allErrors = [...$errors, new UnionCaseErrors($typename, $decoded->errors)];

        return 0 !== count($decoders)
            ? self::decodeRec(
                toDecode: $toDecode,
                inContext: $inContext,
                decoder: $decoders[0],
                decoders: array_slice($decoders, offset: 1),
                errors: $allErrors,
            )
            : invalids([
                new UnionTypeErrors($allErrors),
            ]);
    }
}
