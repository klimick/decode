<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\Factory\ObjectDecoderFactory;
use Klimick\Decode\Decoder\Factory\TaggedUnionDecoderFactory;
use Klimick\Decode\Error\CastException;
use Klimick\Decode\Error\Context;
use Klimick\Decode\Error\DecodeError;
use Klimick\Decode\Report\DefaultReporter;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\IntersectionFunctionReturnTypeProvider;
use Klimick\PsalmDecode\Hook\FunctionReturnTypeProvider\ShapeFunctionReturnTypeProvider;

/**
 * @template T
 *
 * @param DecoderInterface<T> $with
 * @return Either<non-empty-list<DecodeError>, T>
 *
 * @psalm-pure
 */
function decode(mixed $value, DecoderInterface $with): Either
{
    $hasAliases = !empty($with->getAliases());

    if ($hasAliases) {
        return shape(__root_value__: $with)
            ->decode($value, Context::root($with, $value))
            ->map(fn($decoded) => $decoded['__root_value__']);
    }

    return $with->decode($value, Context::root($with, $value));
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $to
 * @return Option<T>
 *
 * @psalm-pure
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
 *
 * @param DecoderInterface<T> $to
 * @return T
 *
 * @throws CastException
 *
 * @psalm-pure
 */
function tryCast(mixed $value, DecoderInterface $to): mixed
{
    $decoded = decode($value, $to);

    return $decoded->isLeft()
        ? throw new CastException(DefaultReporter::report($decoded->get()), $to->name())
        : $decoded->get();
}

/**
 * @param non-empty-list<non-empty-list<DecodeError>> $errors
 * @return Either<non-empty-list<DecodeError>, empty>
 *
 * @psalm-pure
 */
function invalids(array $errors): Either
{
    return Either::left(array_merge(...$errors));
}

/**
 * @param Context<DecoderInterface> $context
 * @return Either<non-empty-list<DecodeError>, empty>
 *
 * @psalm-pure
 */
function invalid(Context $context): Either
{
    return Either::left([
        DecodeError::typeError($context),
    ]);
}

/**
 * @template T
 *
 * @param T $value
 * @return Either<empty, T>
 *
 * @psalm-pure
 */
function valid(mixed $value): Either
{
    return Either::right($value);
}

/**
 * @return DecoderInterface<mixed>
 *
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
 *
 * @psalm-pure
 * @no-named-arguments
 */
function constantly(mixed $value): DecoderInterface
{
    return new ConstantlyDecoder($value);
}

/**
 * @return DecoderInterface<null>
 *
 * @psalm-pure
 */
function null(): DecoderInterface
{
    return new NullDecoder();
}

/**
 * @return DecoderInterface<int>
 *
 * @psalm-pure
 */
function int(): DecoderInterface
{
    return new IntDecoder();
}

/**
 * @return DecoderInterface<float>
 *
 * @psalm-pure
 */
function float(): DecoderInterface
{
    return new FloatDecoder();
}

/**
 * @return DecoderInterface<numeric>
 *
 * @psalm-pure
 */
function numeric(): DecoderInterface
{
    return new NumericDecoder();
}

/**
 * @return DecoderInterface<numeric-string>
 *
 * @psalm-pure
 */
function numericString(): DecoderInterface
{
    return new NumericStringDecoder();
}

/**
 * @return DecoderInterface<bool>
 *
 * @psalm-pure
 */
function bool(): DecoderInterface
{
    return new BoolDecoder();
}

/**
 * @return DecoderInterface<string>
 *
 * @psalm-pure
 */
function string(): DecoderInterface
{
    return new StringDecoder();
}

/**
 * @return DecoderInterface<non-empty-string>
 *
 * @psalm-pure
 */
function nonEmptyString(): DecoderInterface
{
    return new NonEmptyStringDecoder();
}

/**
 * @return DecoderInterface<scalar>
 *
 * @psalm-pure
 */
function scalar(): DecoderInterface
{
    return new ScalarDecoder();
}

/**
 * @return DecoderInterface<DateTimeImmutable>
 *
 * @psalm-pure
 */
function datetime(string $timezone = 'UTC', null|string $fromFormat = null): DecoderInterface
{
    return new DatetimeDecoder($timezone, $fromFormat);
}

/**
 * @return DecoderInterface<array-key>
 *
 * @psalm-pure
 */
function arrayKey(): DecoderInterface
{
    return new ArrayKeyDecoder();
}

/**
 * @template T of scalar
 *
 * @param T $head
 * @param T ...$tail
 * @return DecoderInterface<T>
 *
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
 *
 * @psalm-pure
 * @no-named-arguments
 */
function listOf(DecoderInterface $decoder): DecoderInterface
{
    return new ArrayListOfDecoder($decoder);
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $decoder
 * @return DecoderInterface<non-empty-list<T>>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function nonEmptyListOf(DecoderInterface $decoder): DecoderInterface
{
    return new NonEmptyArrayListOfDecoder($decoder);
}

/**
 * @template K of array-key
 * @template V
 *
 * @param DecoderInterface<K> $key
 * @param DecoderInterface<V> $value
 * @return DecoderInterface<array<K, V>>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function arrayOf(DecoderInterface $key, DecoderInterface $value): DecoderInterface
{
    return new ArrayOfDecoder($key, $value);
}

/**
 * @template K of array-key
 * @template V
 *
 * @param DecoderInterface<K> $key
 * @param DecoderInterface<V> $value
 * @return DecoderInterface<non-empty-array<K, V>>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function nonEmptyArrayOf(DecoderInterface $key, DecoderInterface $value): DecoderInterface
{
    return new NonEmptyArrayOfDecoder($key, $value);
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $decoder
 * @return DecoderInterface<T>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function fromJson(DecoderInterface $decoder): DecoderInterface
{
    return new FromJsonDecoder($decoder);
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $decoder
 * @return DecoderInterface<Option<T>>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function option(DecoderInterface $decoder): DecoderInterface
{
    return new OptionDecoder($decoder);
}

/**
 * @template TLeft
 * @template TRight
 *
 * @param DecoderInterface<TLeft> $left
 * @param DecoderInterface<TRight> $right
 * @return DecoderInterface<Either<TLeft, TRight>>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function either(DecoderInterface $left, DecoderInterface $right): DecoderInterface
{
    return new EitherDecoder($left, $right);
}

/**
 * @return ShapeDecoder<array<array-key, mixed>>
 * @see ShapeFunctionReturnTypeProvider
 *
 * @psalm-pure
 */
function shape(DecoderInterface ...$properties): ShapeDecoder
{
    /**
     * Validated via psalm plugin hook at this moment
     * @psalm-var array<int|string, DecoderInterface> $properties
     */
    return new ShapeDecoder($properties);
}

/**
 * @template T
 *
 * @param class-string<T> $class
 * @return ObjectDecoderFactory<T>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function object(string $class): ObjectDecoderFactory
{
    return new ObjectDecoderFactory($class);
}

/**
 * @template T
 *
 * @param class-string<T> $of
 * @return DecoderInterface<T>
 *
 * @psalm-pure
 */
function instance(string $of): DecoderInterface
{
    return new InstanceofDecoder($of);
}

/**
 * @template T of object
 *
 * @psalm-param pure-callable(): DecoderInterface<T> $type
 * @return DecoderInterface<T>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function rec(callable $type): DecoderInterface
{
    return new RecursionDecoder(Closure::fromCallable($type));
}

/**
 * @template T
 *
 * @param DecoderInterface<T> $head
 * @param DecoderInterface<T> $middle
 * @param DecoderInterface<T> ...$rest
 * @return UnionDecoder<T>
 *
 * @psalm-pure
 * @no-named-arguments
 */
function union(DecoderInterface $head, DecoderInterface $middle, DecoderInterface ...$rest): UnionDecoder
{
    return new UnionDecoder([$head, $middle, ...$rest]);
}

/**
 * @param non-empty-string $with
 *
 * @psalm-pure
 */
function tagged(string $with): TaggedUnionDecoderFactory
{
    return new TaggedUnionDecoderFactory($with);
}

/**
 * @template T of array
 *
 * @param ShapeDecoder<T> $head
 * @param ShapeDecoder<T> $middle
 * @param ShapeDecoder<T> ...$rest
 * @return ShapeDecoder<array<array-key, mixed>>
 *
 * @see IntersectionFunctionReturnTypeProvider
 *
 * @psalm-pure
 * @no-named-arguments
 */
function intersection(ShapeDecoder $head, ShapeDecoder $middle, ShapeDecoder ...$rest): ShapeDecoder
{
    $toMerge = array_map(
        fn(ShapeDecoder $decoder) => $decoder->decoders,
        [$head, $middle, ...$rest],
    );

    return new ShapeDecoder(array_merge(...$toMerge));
}
