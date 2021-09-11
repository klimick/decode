<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\ErrorInterface;
use Klimick\Decode\Internal;
use Klimick\Decode\Context;
use Klimick\Decode\Report\DefaultReporter;
use Klimick\PsalmDecode\ShapeDecoder\PartialShapeReturnTypeProvider;
use Klimick\PsalmDecode\ShapeDecoder\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\ShapeDecoder\TupleReturnTypeProvider;

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T> $with
 * @psalm-return Either<Invalid, Valid<T>>
 */
function decode(mixed $value, AbstractDecoder $with): Either
{
    return $with->decode($value, Context::root($with->name(), $value));
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T> $to
 * @psalm-return Option<T>
 */
function cast(mixed $value, AbstractDecoder $to): Option
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
 * @param AbstractDecoder<T> $to
 * @return T
 *
 * @throws CastException
 */
function tryCast(mixed $value, AbstractDecoder $to): mixed
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
 * @return AbstractDecoder<mixed>
 */
function mixed(): AbstractDecoder
{
    return new Internal\MixedDecoder();
}

/**
 * @template T
 * @psalm-pure
 *
 * @param T $value
 * @return AbstractDecoder<T>
 */
function constant(mixed $value): AbstractDecoder
{
    return new Internal\ConstantDecoder($value);
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<null>
 */
function null(): AbstractDecoder
{
    return new Internal\NullDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<int>
 */
function int(): AbstractDecoder
{
    return new Internal\IntDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<positive-int>
 */
function positiveInt(): AbstractDecoder
{
    return new Internal\PositiveIntDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<float>
 */
function float(): AbstractDecoder
{
    return new Internal\FloatDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<numeric>
 */
function numeric(): AbstractDecoder
{
    return new Internal\NumericDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<numeric-string>
 */
function numericString(): AbstractDecoder
{
    return new Internal\NumericStringDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<bool>
 */
function bool(): AbstractDecoder
{
    return new Internal\BoolDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<string>
 */
function string(): AbstractDecoder
{
    return new Internal\StringDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<non-empty-string>
 */
function nonEmptyString(): AbstractDecoder
{
    return new Internal\NonEmptyStringDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<scalar>
 */
function scalar(): AbstractDecoder
{
    return new Internal\ScalarDecoder();
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<DateTimeImmutable>
 */
function datetime(string $timezone = 'UTC'): AbstractDecoder
{
    return new Internal\DatetimeDecoder($timezone);
}

/**
 * @psalm-pure
 *
 * @return AbstractDecoder<array-key>
 */
function arrKey(): AbstractDecoder
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
 * @return AbstractDecoder<T>
 */
function literal(mixed $head, mixed ...$tail): AbstractDecoder
{
    return new Internal\LiteralDecoder([$head, ...$tail]);
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T> $decoder
 * @return AbstractDecoder<list<T>>
 */
function arrList(AbstractDecoder $decoder): AbstractDecoder
{
    return new Internal\ArrListDecoder($decoder);
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T> $decoder
 * @return AbstractDecoder<non-empty-list<T>>
 */
function nonEmptyArrList(AbstractDecoder $decoder): AbstractDecoder
{
    return new Internal\NonEmptyArrListDecoder($decoder);
}

/**
 * @template K of array-key
 * @template V
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<K> $keyDecoder
 * @psalm-param AbstractDecoder<V> $valDecoder
 *
 * @return AbstractDecoder<array<K, V>>
 */
function arr(AbstractDecoder $keyDecoder, AbstractDecoder $valDecoder): AbstractDecoder
{
    return new Internal\ArrDecoder($keyDecoder, $valDecoder);
}

/**
 * @template K of array-key
 * @template V
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<K> $keyDecoder
 * @psalm-param AbstractDecoder<V> $valDecoder
 *
 * @return AbstractDecoder<non-empty-array<K, V>>
 */
function nonEmptyArr(AbstractDecoder $keyDecoder, AbstractDecoder $valDecoder): AbstractDecoder
{
    return new Internal\NonEmptyArrDecoder($keyDecoder, $valDecoder);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param AbstractDecoder<T> $decoder
 * @return AbstractDecoder<T>
 */
function fromJson(AbstractDecoder $decoder): AbstractDecoder
{
    return new Internal\FromJsonDecoder($decoder);
}

/**
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder ...$decoders
 * @return AbstractDecoder<array<string, mixed>>
 *
 * @see ShapeReturnTypeProvider
 */
function shape(AbstractDecoder ...$decoders): AbstractDecoder
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<string, AbstractDecoder> $decoders
     */
    return new Internal\Shape\ShapeDecoder($decoders);
}

/**
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder ...$decoders
 * @return AbstractDecoder<array<string, mixed>>
 *
 * @see PartialShapeReturnTypeProvider
 */
function partialShape(AbstractDecoder ...$decoders): AbstractDecoder
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<string, AbstractDecoder> $decoders
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
 * @param callable(): AbstractDecoder<T> $type
 * @return AbstractDecoder<T>
 */
function rec(callable $type): AbstractDecoder
{
    return new Internal\RecursionDecoder(Closure::fromCallable($type));
}

/**
 * @template T
 * @psalm-pure
 * @no-named-arguments
 *
 * @psalm-param AbstractDecoder<T> $first
 * @psalm-param AbstractDecoder<T> $second
 * @psalm-param AbstractDecoder<T> ...$rest
 * @psalm-return AbstractDecoder<T> & Internal\UnionDecoder<T>
 */
function union(AbstractDecoder $first, AbstractDecoder $second, AbstractDecoder ...$rest): AbstractDecoder
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
function intersection(AbstractDecoder $first, AbstractDecoder $second, AbstractDecoder ...$rest): AbstractDecoder
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
 * @psalm-param AbstractDecoder $first
 * @psalm-param AbstractDecoder ...$rest
 * @return AbstractDecoder<list<mixed>>
 *
 * @see TupleReturnTypeProvider
 */
function tuple(AbstractDecoder $first, AbstractDecoder ...$rest): AbstractDecoder
{
    return new Internal\TupleDecoder([$first, ...$rest]);
}

function cases(Internal\ObjectDecoder|Internal\UnionDecoder ...$cases): SumCases
{
    /** @psalm-var non-empty-array<non-empty-string, Internal\ObjectDecoder<ProductType> | Internal\UnionDecoder<SumType>> $cases */
    return new SumCases($cases);
}
