<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Internal;
use Klimick\Decode\Context;
use Klimick\Decode\Report\DefaultReporter;
use Klimick\PsalmDecode\ShapeDecoder\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\ShapeDecoder\TupleReturnTypeProvider;

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
 *
 * @return DecoderInterface<mixed>
 */
function mixed(): DecoderInterface
{
    return new Internal\MixedDecoder();
}

/**
 * @template T
 * @psalm-pure
 *
 * @param T $value
 * @return DecoderInterface<T>
 */
function constant(mixed $value): DecoderInterface
{
    return new Internal\ConstantDecoder($value);
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<null>
 */
function null(): DecoderInterface
{
    return new Internal\NullDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<int>
 */
function int(): DecoderInterface
{
    return new Internal\IntDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<positive-int>
 */
function positiveInt(): DecoderInterface
{
    return new Internal\PositiveIntDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<float>
 */
function float(): DecoderInterface
{
    return new Internal\FloatDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<numeric>
 */
function numeric(): DecoderInterface
{
    return new Internal\NumericDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<numeric-string>
 */
function numericString(): DecoderInterface
{
    return new Internal\NumericStringDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<bool>
 */
function bool(): DecoderInterface
{
    return new Internal\BoolDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<string>
 */
function string(): DecoderInterface
{
    return new Internal\StringDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<non-empty-string>
 */
function nonEmptyString(): DecoderInterface
{
    return new Internal\NonEmptyStringDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<scalar>
 */
function scalar(): DecoderInterface
{
    return new Internal\ScalarDecoder();
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<DateTimeImmutable>
 */
function datetime(string $timezone = 'UTC', null|string $fromFormat = null): DecoderInterface
{
    return new Internal\DatetimeDecoder($timezone, $fromFormat);
}

/**
 * @psalm-pure
 *
 * @return DecoderInterface<array-key>
 */
function arrKey(): DecoderInterface
{
    return new Internal\ArrKeyDecoder();
}

/**
 * @template T of scalar
 * @psalm-pure
 * @no-named-arguments
 *
 * @param T $head
 * @param T ...$tail
 * @return DecoderInterface<T>
 */
function literal(mixed $head, mixed ...$tail): DecoderInterface
{
    return new Internal\LiteralDecoder([$head, ...$tail]);
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T> $decoder
 * @return DecoderInterface<list<T>>
 */
function arrList(DecoderInterface $decoder): DecoderInterface
{
    return new Internal\ArrListDecoder($decoder);
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T> $decoder
 * @return DecoderInterface<non-empty-list<T>>
 */
function nonEmptyArrList(DecoderInterface $decoder): DecoderInterface
{
    return new Internal\NonEmptyArrListDecoder($decoder);
}

/**
 * @template K of array-key
 * @template V
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<K> $keyDecoder
 * @psalm-param DecoderInterface<V> $valDecoder
 *
 * @return DecoderInterface<array<K, V>>
 */
function arr(DecoderInterface $keyDecoder, DecoderInterface $valDecoder): DecoderInterface
{
    return new Internal\ArrDecoder($keyDecoder, $valDecoder);
}

/**
 * @template K of array-key
 * @template V
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<K> $keyDecoder
 * @psalm-param DecoderInterface<V> $valDecoder
 *
 * @return DecoderInterface<non-empty-array<K, V>>
 */
function nonEmptyArr(DecoderInterface $keyDecoder, DecoderInterface $valDecoder): DecoderInterface
{
    return new Internal\NonEmptyArrDecoder($keyDecoder, $valDecoder);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param DecoderInterface<T> $decoder
 * @return DecoderInterface<T>
 */
function fromJson(DecoderInterface $decoder): DecoderInterface
{
    return new Internal\FromJsonDecoder($decoder);
}

/**
 * @psalm-pure
 *
 * @psalm-param DecoderInterface ...$decoders
 * @return DecoderInterface<array<string, mixed>>
 *
 * @see ShapeReturnTypeProvider
 */
function shape(DecoderInterface ...$decoders): DecoderInterface
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<string, DecoderInterface> $decoders
     */
    return new Internal\Shape\ShapeDecoder($decoders);
}

/**
 * @psalm-pure
 *
 * @psalm-param DecoderInterface ...$decoders
 * @return DecoderInterface<array<string, mixed>>
 *
 * @see PartialShapeReturnTypeProvider
 */
function partialShape(DecoderInterface ...$decoders): DecoderInterface
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<string, DecoderInterface> $decoders
     */
    return new Internal\Shape\ShapeDecoder($decoders, partial: true);
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
 * @return DecoderInterface<T>
 */
function rec(callable $type): DecoderInterface
{
    return new Internal\RecursionDecoder(Closure::fromCallable($type));
}

/**
 * @template T
 * @psalm-pure
 * @no-named-arguments
 *
 * @psalm-param DecoderInterface<T> $first
 * @psalm-param DecoderInterface<T> $second
 * @psalm-param DecoderInterface<T> ...$rest
 * @psalm-return DecoderInterface<T> & Internal\UnionDecoder<T>
 */
function union(DecoderInterface $first, DecoderInterface $second, DecoderInterface ...$rest): DecoderInterface
{
    return new Internal\UnionDecoder([$first, $second, ...$rest]);
}

/**
 * @template T of array
 * @psalm-pure
 * @no-named-arguments
 *
 * @psalm-param Internal\Shape\ShapeDecoder<T> $first
 * @psalm-param Internal\Shape\ShapeDecoder<T> $second
 * @psalm-param Internal\Shape\ShapeDecoder<T> ...$rest
 */
function intersection(DecoderInterface $first, DecoderInterface $second, DecoderInterface ...$rest): DecoderInterface
{
    $decoders = array_merge(
        ...array_map(fn($decoder) => $decoder->decoders, [$first, $second, ...$rest])
    );

    return new Internal\Shape\ShapeDecoder($decoders);
}

/**
 * @psalm-pure
 * @no-named-arguments
 *
 * @psalm-param DecoderInterface $first
 * @psalm-param DecoderInterface ...$rest
 * @return DecoderInterface<list<mixed>>
 *
 * @see TupleReturnTypeProvider
 */
function tuple(DecoderInterface $first, DecoderInterface ...$rest): DecoderInterface
{
    return new Internal\TupleDecoder([$first, ...$rest]);
}

function cases(Internal\ObjectDecoder|Internal\UnionDecoder ...$cases): SumCases
{
    /** @psalm-var non-empty-array<non-empty-string, Internal\ObjectDecoder<ProductType> | Internal\UnionDecoder<SumType>> $cases */
    return new SumCases($cases);
}

/**
 * @template T
 *
 * @param class-string<T> $class
 * @return Internal\ObjectDecoder<T> | Internal\UnionDecoder<T>
 * @psalm-return (DecoderInterface<T> & Internal\ObjectDecoder<T>) | (DecoderInterface<T> & Internal\UnionDecoder<T>)
 */
function sumType(string $class): DecoderInterface
{
    /**
     * @todo: fix
     * @psalm-suppress MixedMethodCall, MixedReturnStatement
     * @var (DecoderInterface<T> & Internal\ObjectDecoder<T>) | (DecoderInterface<T> & Internal\UnionDecoder<T>)
     */
    return $class::type();
}

/**
 * @template T
 *
 * @param class-string<T> $class
 * @return Internal\ObjectDecoder<T>
 * @psalm-return DecoderInterface<T> & Internal\ObjectDecoder<T>
 */
function productType(string $class): DecoderInterface
{
    /**
     * @todo: fix
     * @psalm-suppress MixedMethodCall, MixedReturnStatement
     * @var DecoderInterface<T> & Internal\ObjectDecoder<T>
     */
    return $class::type();
}
