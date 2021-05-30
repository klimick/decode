<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use DateTimeImmutable;
use Fp\Functional\Either\Either;
use Fp\Functional\Either\Right;
use Klimick\Decode\Decoder\ErrorInterface;
use Klimick\Decode\Internal;
use Klimick\Decode\Context;
use Klimick\Decode\ContextEntry;
use Klimick\PsalmDecode\ShapeDecoder\PartialShapeReturnTypeProvider;
use Klimick\PsalmDecode\ShapeDecoder\ShapeReturnTypeProvider;
use RuntimeException;

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $decoder
 * @psalm-return Either<Invalid, Valid<T>>
 */
function decode(mixed $data, callable|AbstractDecoder $decoder): Either
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
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $decoder
 * @psalm-return T
 */
function cast(mixed $data, callable|AbstractDecoder $decoder): mixed
{
    $decoder = Internal\ToDecoder::for($decoder);

    $decoded = decode($data, $decoder);

    if ($decoded instanceof Right) {
        /** @var Right<Valid<T>> $decoded  */
        return $decoded->get()->value;
    }

    throw new RuntimeException("Cannot cast given value to {$decoder->name()}");
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
function fallback(mixed $value): AbstractDecoder
{
    return new Internal\FallbackDecoder($value);
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
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $decoder
 * @return AbstractDecoder<list<T>>
 */
function arrList(callable|AbstractDecoder $decoder): AbstractDecoder
{
    return new Internal\ArrListDecoder(
        decoder: Internal\ToDecoder::for($decoder)
    );
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $decoder
 * @return AbstractDecoder<non-empty-list<T>>
 */
function nonEmptyArrList(callable|AbstractDecoder $decoder): AbstractDecoder
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
 * @psalm-param AbstractDecoder<K>|pure-callable(): AbstractDecoder<K> $keyDecoder
 * @psalm-param AbstractDecoder<V>|pure-callable(): AbstractDecoder<V> $valDecoder
 *
 * @return AbstractDecoder<array<K, V>>
 */
function arr(callable|AbstractDecoder $keyDecoder, callable|AbstractDecoder $valDecoder): AbstractDecoder
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
 * @psalm-param AbstractDecoder<K>|pure-callable(): AbstractDecoder<K> $keyDecoder
 * @psalm-param AbstractDecoder<V>|pure-callable(): AbstractDecoder<V> $valDecoder
 *
 * @return AbstractDecoder<non-empty-array<K, V>>
 */
function nonEmptyArr(callable|AbstractDecoder $keyDecoder, callable|AbstractDecoder $valDecoder): AbstractDecoder
{
    return new Internal\NonEmptyArrDecoder(
        keyDecoder: Internal\ToDecoder::for($keyDecoder),
        valDecoder: Internal\ToDecoder::for($valDecoder),
    );
}

/**
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder|pure-callable(): AbstractDecoder ...$decoders
 * @return AbstractDecoder<array<string, mixed>>
 *
 * @see ShapeReturnTypeProvider
 */
function shape(callable|AbstractDecoder ...$decoders): AbstractDecoder
{
    return new Internal\Shape\ShapeDecoder(
        decoders: Internal\ToDecoder::forAll($decoders)
    );
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> ...$decoders
 * @return AbstractDecoder<array<string, T>>
 *
 * @see PartialShapeReturnTypeProvider
 */
function partialShape(callable|AbstractDecoder ...$decoders): AbstractDecoder
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
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $first
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $second
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> ...$rest
 * @return AbstractDecoder<T>
 */
function union(callable|AbstractDecoder $first, callable|AbstractDecoder $second, callable|AbstractDecoder ...$rest): AbstractDecoder
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
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $first
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $second
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> ...$rest
 * @return AbstractDecoder<T>
 */
function intersection(callable|AbstractDecoder $first, callable|AbstractDecoder $second, callable|AbstractDecoder ...$rest): AbstractDecoder
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
 * @psalm-param ((pure-callable(): AbstractDecoder<T>)| (AbstractDecoder<T>)) $decoder
 * @return AbstractDecoder<T>
 */
function optional(callable|AbstractDecoder $decoder): AbstractDecoder
{
    return new Internal\HighOrder\OptionalDecoder(Internal\ToDecoder::for($decoder));
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $decoder
 * @param non-empty-string $alias
 * @return AbstractDecoder<T>
 */
function aliased(callable|AbstractDecoder $decoder, string $alias): AbstractDecoder
{
    return new Internal\HighOrder\AliasedDecoder($alias, Internal\ToDecoder::for($decoder));
}

/**
 * @template T
 * @psalm-pure
 *
 * @psalm-param AbstractDecoder<T>|pure-callable(): AbstractDecoder<T> $decoder
 * @return AbstractDecoder<T>
 */
function fromSelf(callable|AbstractDecoder $decoder): AbstractDecoder
{
    return new Internal\HighOrder\FromSelfDecoder(Internal\ToDecoder::for($decoder));
}
