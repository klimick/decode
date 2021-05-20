<?php

declare(strict_types=1);

namespace Klimick\Decode;

use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Fp\Functional\Either\Right;
use Klimick\Decode\Internal;
use Klimick\PsalmDecode\ShapeDecoder\PartialShapeReturnTypeProvider;
use Klimick\PsalmDecode\ShapeDecoder\ShapeReturnTypeProvider;
use RuntimeException;

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $decoder
 * @psalm-return Either<Invalid, Valid<T>>
 */
function decode(callable|DecoderInterface $decoder, mixed $data): Either
{
    $decoder = Internal\ToDecoder::for($decoder);

    $context = new Context([
        new ContextEntry($decoder->name(), $data),
    ]);

    return $decoder->decode($data, $context);
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $decoder
 * @psalm-return T
 */
function cast(callable|DecoderInterface $decoder, mixed $data): mixed
{
    $decoder = Internal\ToDecoder::for($decoder);

    $decoded = decode($decoder, $data);

    if ($decoded instanceof Right) {
        return $decoded->get()->value;
    }

    throw new RuntimeException("Cannot cast given value to {$decoder->name()}");
}

/**
 * @psalm-pure
 *
 * @param non-empty-list<TypeError> $errors
 * @return Either<Invalid, empty>
 */
function invalids(array $errors): Either
{
    /** @psalm-suppress ImpureMethodCall */
    return Either::left(new Invalid($errors));
}

/**
 * @psalm-pure
 *
 * @return Either<Invalid, empty>
 */
function invalid(Context $context, array $payload = []): Either
{
    return invalids([
        new TypeError($context, $payload),
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
    /** @psalm-suppress ImpureMethodCall */
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
function fallback(mixed $value): DecoderInterface
{
    return new Internal\FallbackDecoder($value);
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
function datetime(string $timezone = 'UTC'): DecoderInterface
{
    return new Internal\DatetimeDecoder($timezone);
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
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $decoder
 * @return DecoderInterface<list<T>>
 */
function arrList(callable|DecoderInterface $decoder): DecoderInterface
{
    return new Internal\ArrListDecoder(
        decoder: Internal\ToDecoder::for($decoder)
    );
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $decoder
 * @return DecoderInterface<non-empty-list<T>>
 */
function nonEmptyArrList(callable|DecoderInterface $decoder): DecoderInterface
{
    return new Internal\NonEmptyArrListDecoder(
        decoder: Internal\ToDecoder::for($decoder)
    );
}

/**
 * @template K of array-key
 * @template V
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<K>|pure-callable(): DecoderInterface<K> $keyDecoder
 * @psalm-param DecoderInterface<V>|pure-callable(): DecoderInterface<V> $valDecoder
 *
 * @return DecoderInterface<array<K, V>>
 */
function arr(callable|DecoderInterface $keyDecoder, callable|DecoderInterface $valDecoder): DecoderInterface
{
    return new Internal\ArrDecoder(
        keyDecoder: Internal\ToDecoder::for($keyDecoder),
        valDecoder: Internal\ToDecoder::for($valDecoder),
    );
}

/**
 * @template K of array-key
 * @template V
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<K>|pure-callable(): DecoderInterface<K> $keyDecoder
 * @psalm-param DecoderInterface<V>|pure-callable(): DecoderInterface<V> $valDecoder
 *
 * @return DecoderInterface<non-empty-array<K, V>>
 */
function nonEmptyArr(callable|DecoderInterface $keyDecoder, callable|DecoderInterface $valDecoder): DecoderInterface
{
    return new Internal\NonEmptyArrDecoder(
        keyDecoder: Internal\ToDecoder::for($keyDecoder),
        valDecoder: Internal\ToDecoder::for($valDecoder),
    );
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> ...$decoders
 * @return DecoderInterface<array<string, T>>
 *
 * @see ShapeReturnTypeProvider
 */
function shape(callable|DecoderInterface ...$decoders): DecoderInterface
{
    return new Internal\Shape\ShapeDecoder(
        decoders: Internal\ToDecoder::forAll($decoders)
    );
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> ...$decoders
 * @return DecoderInterface<array<string, T>>
 *
 * @see PartialShapeReturnTypeProvider
 */
function partialShape(callable|DecoderInterface ...$decoders): DecoderInterface
{
    return new Internal\Shape\ShapeDecoder(
        decoders: Internal\ToDecoder::forAll($decoders),
        partial: true,
    );
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
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $first
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $second
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> ...$rest
 * @return DecoderInterface<T>
 */
function union(callable|DecoderInterface $first, callable|DecoderInterface $second, callable|DecoderInterface ...$rest): DecoderInterface
{
    $restDecoders = array_values(Internal\ToDecoder::forAll($rest));

    return new Internal\UnionDecoder(
        Internal\ToDecoder::for($first),
        Internal\ToDecoder::for($second),
        ...$restDecoders,
    );
}

/**
 * @template T of array
 * @psalm-pure
 * @no-named-arguments
 *
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $first
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $second
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> ...$rest
 * @return DecoderInterface<T>
 */
function intersection(callable|DecoderInterface $first, callable|DecoderInterface $second, callable|DecoderInterface ...$rest): DecoderInterface
{
    $restDecoders = array_values(Internal\ToDecoder::forAll($rest));

    return new Internal\IntersectionDecoder(
        Internal\ToDecoder::for($first),
        Internal\ToDecoder::for($second),
        ...$restDecoders,
    );
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $decoder
 * @param non-empty-string $alias
 * @return DecoderInterface<T>
 */
function aliased(callable|DecoderInterface $decoder, string $alias): DecoderInterface
{
    return new Internal\HighOrder\AliasedDecoder($alias, Internal\ToDecoder::for($decoder));
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param DecoderInterface<T>|pure-callable(): DecoderInterface<T> $decoder
 * @return DecoderInterface<T>
 */
function fromSelf(callable|DecoderInterface $decoder): DecoderInterface
{
    return new Internal\HighOrder\FromSelfDecoder(Internal\ToDecoder::for($decoder));
}
