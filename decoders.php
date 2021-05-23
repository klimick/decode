<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Closure;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Fp\Functional\Either\Right;
use Klimick\Decode\Error\ErrorInterface;
use Klimick\Decode\Error\TypeError;
use Klimick\Decode\Internal;
use Klimick\PsalmDecode\ShapeDecoder\PartialShapeReturnTypeProvider;
use Klimick\PsalmDecode\ShapeDecoder\ShapeReturnTypeProvider;
use RuntimeException;

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $decoder
 * @psalm-return Either<Invalid, Valid<T>>
 */
function decode(mixed $data, callable|Decoder $decoder): Either
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
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $decoder
 * @psalm-return T
 */
function cast(mixed $data, callable|Decoder $decoder): mixed
{
    $decoder = Internal\ToDecoder::for($decoder);

    $decoded = decode($data, $decoder);

    if ($decoded instanceof Right) {
        return $decoded->get()->value;
    }

    throw new RuntimeException("Cannot cast given value to {$decoder->name()}");
}

/**
 * @psalm-pure
 *
 * @param non-empty-list<ErrorInterface> $errors
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
    /** @psalm-suppress ImpureMethodCall */
    return Either::right(new Valid($value));
}

/**
 * @psalm-pure
 *
 * @return Decoder<mixed>
 */
function mixed(): Decoder
{
    return new Internal\MixedDecoder();
}

/**
 * @template T
 * @psalm-pure
 *
 * @param T $value
 * @return Decoder<T>
 */
function fallback(mixed $value): Decoder
{
    return new Internal\FallbackDecoder($value);
}

/**
 * @psalm-pure
 *
 * @return Decoder<null>
 */
function null(): Decoder
{
    return new Internal\NullDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<int>
 */
function int(): Decoder
{
    return new Internal\IntDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<positive-int>
 */
function positiveInt(): Decoder
{
    return new Internal\PositiveIntDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<float>
 */
function float(): Decoder
{
    return new Internal\FloatDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<numeric>
 */
function numeric(): Decoder
{
    return new Internal\NumericDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<numeric-string>
 */
function numericString(): Decoder
{
    return new Internal\NumericStringDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<bool>
 */
function bool(): Decoder
{
    return new Internal\BoolDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<string>
 */
function string(): Decoder
{
    return new Internal\StringDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<non-empty-string>
 */
function nonEmptyString(): Decoder
{
    return new Internal\NonEmptyStringDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<scalar>
 */
function scalar(): Decoder
{
    return new Internal\ScalarDecoder();
}

/**
 * @psalm-pure
 *
 * @return Decoder<DateTimeImmutable>
 */
function datetime(string $timezone = 'UTC'): Decoder
{
    return new Internal\DatetimeDecoder($timezone);
}

/**
 * @psalm-pure
 *
 * @return Decoder<array-key>
 */
function arrKey(): Decoder
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
 * @return Decoder<T>
 */
function literal(mixed $head, mixed ...$tail): Decoder
{
    return new Internal\LiteralDecoder([$head, ...$tail]);
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $decoder
 * @return Decoder<list<T>>
 */
function arrList(callable|Decoder $decoder): Decoder
{
    return new Internal\ArrListDecoder(
        decoder: Internal\ToDecoder::for($decoder)
    );
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $decoder
 * @return Decoder<non-empty-list<T>>
 */
function nonEmptyArrList(callable|Decoder $decoder): Decoder
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
 * @psalm-param Decoder<K>|pure-callable(): Decoder<K> $keyDecoder
 * @psalm-param Decoder<V>|pure-callable(): Decoder<V> $valDecoder
 *
 * @return Decoder<array<K, V>>
 */
function arr(callable|Decoder $keyDecoder, callable|Decoder $valDecoder): Decoder
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
 * @psalm-param Decoder<K>|pure-callable(): Decoder<K> $keyDecoder
 * @psalm-param Decoder<V>|pure-callable(): Decoder<V> $valDecoder
 *
 * @return Decoder<non-empty-array<K, V>>
 */
function nonEmptyArr(callable|Decoder $keyDecoder, callable|Decoder $valDecoder): Decoder
{
    return new Internal\NonEmptyArrDecoder(
        keyDecoder: Internal\ToDecoder::for($keyDecoder),
        valDecoder: Internal\ToDecoder::for($valDecoder),
    );
}

/**
 * @psalm-pure
 *
 * @psalm-param Decoder|pure-callable(): Decoder ...$decoders
 * @return Decoder<array<string, mixed>>
 *
 * @see ShapeReturnTypeProvider
 */
function shape(callable|Decoder ...$decoders): Decoder
{
    return new Internal\Shape\ShapeDecoder(
        decoders: Internal\ToDecoder::forAll($decoders)
    );
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> ...$decoders
 * @return Decoder<array<string, T>>
 *
 * @see PartialShapeReturnTypeProvider
 */
function partialShape(callable|Decoder ...$decoders): Decoder
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
 * @param callable(): Decoder<T> $type
 * @return Decoder<T>
 */
function rec(callable $type): Decoder
{
    return new Internal\RecursionDecoder(Closure::fromCallable($type));
}

/**
 * @template T
 * @psalm-pure
 * @no-named-arguments
 *
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $first
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $second
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> ...$rest
 * @return Decoder<T>
 */
function union(callable|Decoder $first, callable|Decoder $second, callable|Decoder ...$rest): Decoder
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
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $first
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $second
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> ...$rest
 * @return Decoder<T>
 */
function intersection(callable|Decoder $first, callable|Decoder $second, callable|Decoder ...$rest): Decoder
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
 * @psalm-param ((pure-callable(): Decoder<T>)| (Decoder<T>)) $decoder
 * @return Decoder<T>
 */
function optional(callable|Decoder $decoder): Decoder
{
    return new Internal\HighOrder\OptionalDecoder(Internal\ToDecoder::for($decoder));
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $decoder
 * @param non-empty-string $alias
 * @return Decoder<T>
 */
function aliased(callable|Decoder $decoder, string $alias): Decoder
{
    return new Internal\HighOrder\AliasedDecoder($alias, Internal\ToDecoder::for($decoder));
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param Decoder<T>|pure-callable(): Decoder<T> $decoder
 * @return Decoder<T>
 */
function fromSelf(callable|Decoder $decoder): Decoder
{
    return new Internal\HighOrder\FromSelfDecoder(Internal\ToDecoder::for($decoder));
}
