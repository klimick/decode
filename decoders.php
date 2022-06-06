<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Error\CastException;
use Klimick\Decode\Decoder\Error\DecodeErrorInterface;
use Klimick\Decode\Decoder\Error\TypeError;
use Klimick\Decode\Decoder\Factory\ObjectDecoderFactory;
use Klimick\Decode\Decoder\Factory\TaggedUnionDecoderFactory;
use Klimick\Decode\Report\DefaultReporter;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\IntersectionReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\ShapeReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\TupleReturnTypeProvider;

/**
 * @template T
 * @psalm-pure
 *
 * @param DecoderInterface<T> $with
 * @return Either<non-empty-list<DecodeErrorInterface>, T>
 */
function decode(mixed $value, DecoderInterface $with): Either
{
    return $with->decode($value, Context::root($with->name(), $value));
}

/**
 * @template T
 * @psalm-pure
 *
 * @param DecoderInterface<T> $to
 * @return Option<T>
 */
function cast(mixed $value, DecoderInterface $to): Option
{
    $decoded = decode($value, $to);

    return $decoded->isLeft()
        ? Option::none()
        : Option::some($decoded->get());
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
    $decoded = decode($value, $to);

    return $decoded->isLeft()
        ? throw new CastException(DefaultReporter::report($decoded->get()), $to->name())
        : $decoded->get();
}

/**
 * @psalm-pure
 *
 * @param non-empty-list<non-empty-list<DecodeErrorInterface>> $errors
 * @return Either<non-empty-list<DecodeErrorInterface>, empty>
 */
function invalids(array $errors): Either
{
    return Either::left(array_merge(...$errors));
}

/**
 * @psalm-pure
 *
 * @return Either<non-empty-list<DecodeErrorInterface>, empty>
 */
function invalid(Context $context): Either
{
    return Either::left([
        new TypeError($context),
    ]);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param T $value
 * @return Either<empty, T>
 */
function valid(mixed $value): Either
{
    return Either::right($value);
}

/**
 * @return DecoderInterface<mixed>
 * @psalm-pure
 */
function mixed(): DecoderInterface
{
    return new MixedDecoder();
}

/**
 * @template T
 *
 * @param T $value
 * @return DecoderInterface<T>
 * @psalm-pure
 */
function constant(mixed $value): DecoderInterface
{
    return new ConstantDecoder($value);
}

/**
 * @return DecoderInterface<null>
 * @psalm-pure
 */
function null(): DecoderInterface
{
    return new NullDecoder();
}

/**
 * @return DecoderInterface<int>
 * @psalm-pure
 */
function int(): DecoderInterface
{
    return new IntDecoder();
}

/**
 * @return DecoderInterface<positive-int>
 * @psalm-pure
 */
function positiveInt(): DecoderInterface
{
    return new PositiveIntDecoder();
}

/**
 * @return DecoderInterface<float>
 * @psalm-pure
 */
function float(): DecoderInterface
{
    return new FloatDecoder();
}

/**
 * @return DecoderInterface<numeric>
 * @psalm-pure
 */
function numeric(): DecoderInterface
{
    return new NumericDecoder();
}

/**
 * @return DecoderInterface<numeric-string>
 * @psalm-pure
 */
function numericString(): DecoderInterface
{
    return new NumericStringDecoder();
}

/**
 * @return DecoderInterface<bool>
 * @psalm-pure
 */
function bool(): DecoderInterface
{
    return new BoolDecoder();
}

/**
 * @return DecoderInterface<string>
 * @psalm-pure
 */
function string(): DecoderInterface
{
    return new StringDecoder();
}

/**
 * @return DecoderInterface<non-empty-string>
 * @psalm-pure
 */
function nonEmptyString(): DecoderInterface
{
    return new NonEmptyStringDecoder();
}

/**
 * @return DecoderInterface<scalar>
 * @psalm-pure
 */
function scalar(): DecoderInterface
{
    return new ScalarDecoder();
}

/**
 * @return DecoderInterface<DateTimeImmutable>
 * @psalm-pure
 */
function datetime(string $timezone = 'UTC', null|string $fromFormat = null): DecoderInterface
{
    return new DatetimeDecoder($timezone, $fromFormat);
}

/**
 * @return DecoderInterface<array-key>
 * @psalm-pure
 */
function arrKey(): DecoderInterface
{
    return new ArrKeyDecoder();
}

/**
 * @template T of scalar
 *
 * @param T $head
 * @param T ...$tail
 * @return DecoderInterface<T>
 * @psalm-pure
 * @no-named-arguments
 */
function literal(mixed $head, mixed ...$tail): DecoderInterface
{
    return new LiteralDecoder([$head, ...$tail]);
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $decoder
 * @return DecoderInterface<list<T>>
 * @psalm-pure
 */
function listOf(DecoderInterface $decoder): DecoderInterface
{
    return new ArrListDecoder($decoder);
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $decoder
 * @return DecoderInterface<non-empty-list<T>>
 * @psalm-pure
 */
function nonEmptyListOf(DecoderInterface $decoder): DecoderInterface
{
    return new NonEmptyArrListDecoder($decoder);
}

/**
 * @template K of array-key
 * @template V
 *
 * @param DecoderInterface<K> $keyDecoder
 * @param DecoderInterface<V> $valDecoder
 * @return DecoderInterface<array<K, V>>
 * @psalm-pure
 */
function arrayOf(DecoderInterface $keyDecoder, DecoderInterface $valDecoder): DecoderInterface
{
    return new ArrDecoder($keyDecoder, $valDecoder);
}

/**
 * @template K of array-key
 * @template V
 *
 * @param DecoderInterface<K> $keyDecoder
 * @param DecoderInterface<V> $valDecoder
 * @return DecoderInterface<non-empty-array<K, V>>
 * @psalm-pure
 */
function nonEmptyArrayOf(DecoderInterface $keyDecoder, DecoderInterface $valDecoder): DecoderInterface
{
    return new NonEmptyArrDecoder($keyDecoder, $valDecoder);
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $decoder
 * @return DecoderInterface<T>
 * @psalm-pure
 */
function fromJson(DecoderInterface $decoder): DecoderInterface
{
    return new FromJsonDecoder($decoder);
}

/**
 * @return DecoderInterface<array<string, mixed>> & ShapeDecoder<array<string, mixed>>
 *
 * @psalm-pure
 * @see ShapeReturnTypeProvider
 */
function shape(DecoderInterface ...$decoders): DecoderInterface
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<string, DecoderInterface> $decoders
     */
    return new ShapeDecoder($decoders);
}

/**
 * @return DecoderInterface<array<string, mixed>> & ShapeDecoder<array<string, mixed>>
 *
 * @psalm-pure
 * @see ShapeReturnTypeProvider
 */
function partialShape(DecoderInterface ...$decoders): DecoderInterface
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<string, DecoderInterface> $decoders
     */
    return new ShapeDecoder($decoders, partial: true);
}

/**
 * @template T
 *
 * @param class-string<T> $objectClass
 * @return ObjectDecoderFactory<T, false>
 * @psalm-pure
 */
function object(string $objectClass): ObjectDecoderFactory
{
    return new ObjectDecoderFactory($objectClass, partial: false);
}

/**
 * @template T
 *
 * @param class-string<T> $of
 * @return DecoderInterface<T>
 * @psalm-pure
 */
function instance(string $of): DecoderInterface
{
    return new InstanceofDecoder($of);
}

/**
 * @template T
 *
 * @param class-string<T> $objectClass
 * @return ObjectDecoderFactory<T, true>
 * @psalm-pure
 */
function partialObject(string $objectClass): ObjectDecoderFactory
{
    return new ObjectDecoderFactory($objectClass, partial: true);
}

/**
 * @template T of object
 *
 * @psalm-param pure-callable(): DecoderInterface<T> $type
 * @return DecoderInterface<T>
 * @psalm-pure
 */
function rec(callable $type): DecoderInterface
{
    return new RecursionDecoder(Closure::fromCallable($type));
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $first
 * @param DecoderInterface<T> $second
 * @param DecoderInterface<T> ...$rest
 * @return DecoderInterface<T> & UnionDecoder<T>
 * @psalm-pure
 * @no-named-arguments
 */
function union(DecoderInterface $first, DecoderInterface $second, DecoderInterface ...$rest): DecoderInterface
{
    return new UnionDecoder([$first, $second, ...$rest]);
}

/**
 * @param non-empty-string $with
 * @psalm-pure
 */
function tagged(string $with): TaggedUnionDecoderFactory
{
    return new TaggedUnionDecoderFactory($with);
}

/**
 * @template T of array
 *
 * @param ShapeDecoder<T> $first
 * @param ShapeDecoder<T> $second
 * @param ShapeDecoder<T> ...$rest
 * @return DecoderInterface<array<string, mixed>> & ShapeDecoder<array<string, mixed>>
 *
 * @psalm-pure
 * @no-named-arguments
 * @see IntersectionReturnTypeProvider
 */
function intersection(ShapeDecoder $first, ShapeDecoder $second, ShapeDecoder ...$rest): DecoderInterface
{
    $toMerge = array_map(
        fn(ShapeDecoder $decoder) => $decoder->partial
            ? array_map(fn(DecoderInterface $d) => $d->optional(), $decoder->decoders)
            : $decoder->decoders,
        [$first, $second, ...$rest],
    );

    return new ShapeDecoder(array_merge(...$toMerge));
}

/**
 * @psalm-pure
 * @no-named-arguments
 * @see TupleReturnTypeProvider
 */
function tuple(DecoderInterface $first, DecoderInterface ...$rest): DecoderInterface
{
    return new TupleDecoder([$first, ...$rest]);
}
