<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Context;
use Klimick\Decode\Internal\ArrDecoder;
use Klimick\Decode\Internal\ArrKeyDecoder;
use Klimick\Decode\Internal\ArrListDecoder;
use Klimick\Decode\Internal\BoolDecoder;
use Klimick\Decode\Internal\ConstantDecoder;
use Klimick\Decode\Internal\DatetimeDecoder;
use Klimick\Decode\Internal\FloatDecoder;
use Klimick\Decode\Internal\FromJsonDecoder;
use Klimick\Decode\Internal\IntDecoder;
use Klimick\Decode\Internal\LiteralDecoder;
use Klimick\Decode\Internal\MixedDecoder;
use Klimick\Decode\Internal\NonEmptyArrDecoder;
use Klimick\Decode\Internal\NonEmptyArrListDecoder;
use Klimick\Decode\Internal\NonEmptyStringDecoder;
use Klimick\Decode\Internal\NullDecoder;
use Klimick\Decode\Internal\NumericDecoder;
use Klimick\Decode\Internal\NumericStringDecoder;
use Klimick\Decode\Internal\ObjectDecoder;
use Klimick\Decode\Internal\PositiveIntDecoder;
use Klimick\Decode\Internal\RecursionDecoder;
use Klimick\Decode\Internal\ScalarDecoder;
use Klimick\Decode\Internal\Shape\ShapeDecoder;
use Klimick\Decode\Internal\StringDecoder;
use Klimick\Decode\Internal\TupleDecoder;
use Klimick\Decode\Internal\UnionDecoder;
use Klimick\Decode\Report\DefaultReporter;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\TupleReturnTypeProvider;
use Klimick\Decode\Decoder\SumType;
use Klimick\Decode\Decoder\ProductType;
use Klimick\Decode\Decoder\Runtype;

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T> $with
 * @psalm-return Either<Invalid, Valid<T>>
 */
function decode(mixed $value, DecoderInterface $with): Either
{
    return $with->decode($value, Context::root($with->name(), $value));
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T> $to
 * @psalm-return Option<T>
 */
function cast(mixed $value, DecoderInterface $to): Option
{
    $decoded = decode($value, $to)->get();

    return $decoded instanceof Invalid
        ? Option::none()
        : Option::some($decoded->value);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param DecoderInterface<T> $to
 * @return T
 *
 * @throws CastException
 */
function tryCast(mixed $value, DecoderInterface $to): mixed
{
    $decoded = decode($value, $to)->get();

    return $decoded instanceof Invalid
        ? throw new CastException(DefaultReporter::report($decoded), $to->name())
        : $decoded->value;
}

/**
 * @psalm-pure
 *
 * @param non-empty-list<DecodeErrorInterface> $errors
 * @return Either<Invalid, empty>
 */
function invalids(array $errors): Either
{
    return Either::left(new Invalid($errors));
}

/**
 * @psalm-pure
 *
 * @return Either<Invalid, empty>
 */
function invalid(Context $context): Either
{
    return invalids([
        new TypeError($context),
    ]);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param T $value
 * @return Either<empty, Valid<T>>
 */
function valid(mixed $value): Either
{
    return Either::right(new Valid($value));
}

/**
 * @psalm-pure
 */
function mixed(): MixedDecoder
{
    return new MixedDecoder();
}

/**
 * @template T
 * @psalm-pure
 *
 * @param T $value
 * @return ConstantDecoder<T>
 */
function constant(mixed $value): ConstantDecoder
{
    return new ConstantDecoder($value);
}

/**
 * @psalm-pure
 */
function null(): NullDecoder
{
    return new NullDecoder();
}

/**
 * @psalm-pure
 */
function int(): IntDecoder
{
    return new IntDecoder();
}

/**
 * @psalm-pure
 */
function positiveInt(): PositiveIntDecoder
{
    return new PositiveIntDecoder();
}

/**
 * @psalm-pure
 */
function float(): FloatDecoder
{
    return new FloatDecoder();
}

/**
 * @psalm-pure
 */
function numeric(): NumericDecoder
{
    return new NumericDecoder();
}

/**
 * @psalm-pure
 */
function numericString(): NumericStringDecoder
{
    return new NumericStringDecoder();
}

/**
 * @psalm-pure
 */
function bool(): BoolDecoder
{
    return new BoolDecoder();
}

/**
 * @psalm-pure
 */
function string(): StringDecoder
{
    return new StringDecoder();
}

/**
 * @psalm-pure
 */
function nonEmptyString(): NonEmptyStringDecoder
{
    return new NonEmptyStringDecoder();
}

/**
 * @psalm-pure
 */
function scalar(): ScalarDecoder
{
    return new ScalarDecoder();
}

/**
 * @psalm-pure
 */
function datetime(string $timezone = 'UTC', null|string $fromFormat = null): DatetimeDecoder
{
    return new DatetimeDecoder($timezone, $fromFormat);
}

/**
 * @psalm-pure
 */
function arrKey(): ArrKeyDecoder
{
    return new ArrKeyDecoder();
}

/**
 * @template T of scalar
 * @psalm-pure
 * @no-named-arguments
 *
 * @param T $head
 * @param T ...$tail
 * @return LiteralDecoder<T>
 */
function literal(mixed $head, mixed ...$tail): LiteralDecoder
{
    return new LiteralDecoder([$head, ...$tail]);
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T> $decoder
 * @return ArrListDecoder<T>
 */
function arrList(DecoderInterface $decoder): ArrListDecoder
{
    return new ArrListDecoder($decoder);
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T> $decoder
 * @return NonEmptyArrListDecoder<T>
 */
function nonEmptyArrList(DecoderInterface $decoder): NonEmptyArrListDecoder
{
    return new NonEmptyArrListDecoder($decoder);
}

/**
 * @template K of array-key
 * @template V
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<K> $keyDecoder
 * @psalm-param DecoderInterface<V> $valDecoder
 *
 * @return ArrDecoder<K, V>
 */
function arr(DecoderInterface $keyDecoder, DecoderInterface $valDecoder): ArrDecoder
{
    return new ArrDecoder($keyDecoder, $valDecoder);
}

/**
 * @template K of array-key
 * @template V
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<K> $keyDecoder
 * @psalm-param DecoderInterface<V> $valDecoder
 *
 * @return NonEmptyArrDecoder<K, V>
 */
function nonEmptyArr(DecoderInterface $keyDecoder, DecoderInterface $valDecoder): NonEmptyArrDecoder
{
    return new NonEmptyArrDecoder($keyDecoder, $valDecoder);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param DecoderInterface<T> $decoder
 * @return FromJsonDecoder<T>
 */
function fromJson(DecoderInterface $decoder): FromJsonDecoder
{
    return new FromJsonDecoder($decoder);
}

/**
 * @psalm-pure
 * @see ShapeReturnTypeProvider
 */
function shape(DecoderInterface ...$decoders): ShapeDecoder
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<string, DecoderInterface> $decoders
     */
    return new ShapeDecoder($decoders);
}

/**
 * @psalm-pure
 * @see ShapeReturnTypeProvider
 */
function partialShape(DecoderInterface ...$decoders): ShapeDecoder
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<string, DecoderInterface> $decoders
     */
    return new ShapeDecoder($decoders, partial: true);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param class-string<T> $objectClass
 * @return ObjectDecoderFactory<T, false>
 */
function object(string $objectClass): ObjectDecoderFactory
{
    return new ObjectDecoderFactory($objectClass, partial: false);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param class-string<T> $objectClass
 * @return ObjectDecoderFactory<T, true>
 */
function partialObject(string $objectClass): ObjectDecoderFactory
{
    return new ObjectDecoderFactory($objectClass, partial: true);
}

/**
 * @template T of object
 * @psalm-pure
 *
 * @param callable(): DecoderInterface<T> $type
 * @return RecursionDecoder<T>
 */
function rec(callable $type): RecursionDecoder
{
    return new RecursionDecoder(Closure::fromCallable($type));
}

/**
 * @template T
 * @psalm-pure
 * @no-named-arguments
 *
 * @psalm-param DecoderInterface<T> $first
 * @psalm-param DecoderInterface<T> $second
 * @psalm-param DecoderInterface<T> ...$rest
 * @psalm-return UnionDecoder<T>
 */
function union(DecoderInterface $first, DecoderInterface $second, DecoderInterface ...$rest): UnionDecoder
{
    return new UnionDecoder([$first, $second, ...$rest]);
}

/**
 * @psalm-pure
 * @no-named-arguments
 */
function intersection(ShapeDecoder $first, ShapeDecoder $second, ShapeDecoder ...$rest): ShapeDecoder
{
    $decoders = array_merge(
        ...array_map(fn($decoder) => $decoder->decoders, [$first, $second, ...$rest])
    );

    return new ShapeDecoder($decoders);
}

/**
 * @psalm-pure
 * @no-named-arguments
 * @see TupleReturnTypeProvider
 */
function tuple(DecoderInterface $first, DecoderInterface ...$rest): TupleDecoder
{
    return new TupleDecoder([$first, ...$rest]);
}

/**
 * @template T of object
 *
 * @param DecoderInterface<T> ...$cases
 * @return SumCases<T>
 *
 * @todo: fix signature
 * @psalm-suppress InvalidReturnType, InvalidReturnStatement
 */
function cases(DecoderInterface ...$cases): SumCases
{
    /**
     * @var non-empty-array<non-empty-string, ObjectDecoder<ProductType> | UnionDecoder<SumType>> $cases
     */
    return new SumCases($cases);
}

/**
 * @template T of SumType
 *
 * @param class-string<T> $class
 * @return UnionDecoder<T>
 */
function sumType(string $class): UnionDecoder
{
    return $class::type();
}

/**
 * @template T of Runtype
 *
 * @param class-string<T> $class
 * @return ObjectDecoder<T>|UnionDecoder<T>
 *
 * @todo: fix signature
 * @psalm-suppress InvalidReturnStatement, InvalidReturnType
 */
function productType(string $class): ObjectDecoder|UnionDecoder
{
    /** @var class-string<ProductType> | class-string<SumType> $class */
    return $class::type();
}
