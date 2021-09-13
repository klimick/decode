<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;
use Klimick\Decode\Internal\Constraint as C;
use Fp\Functional\Either\Either;

/**
 * @psalm-pure
 *
 * @return Either<empty, Valid>
 */
function valid(): Either
{
    return Either::right(new Valid());
}

/**
 * @psalm-pure
 *
 * @return Either<Invalid, empty>
 */
function invalid(Context $context, ConstraintInterface $constraint, array $payload = []): Either
{
    return invalids([
        new ConstraintError($context, $constraint->name(), $payload)
    ]);
}

/**
 * @psalm-pure
 *
 * @param non-empty-list<ConstraintError> $errors
 * @return Either<Invalid, empty>
 */
function invalids(array $errors): Either
{
    return Either::left(new Invalid($errors));
}

/**
 * @template T
 * @psalm-pure
 *
 * @param T $to
 * @return ConstraintInterface<T>
 */
function equal(mixed $to): ConstraintInterface
{
    return new C\Generic\EqualConstraint($to);
}

/**
 * @psalm-pure
 *
 * @param numeric $than
 * @return ConstraintInterface<numeric>
 */
function greater(mixed $than): ConstraintInterface
{
    return new C\Numeric\ComparisonConstraint(
        type: C\Numeric\ComparisonConstraint::OP_GREATER,
        value: $than,
    );
}

/**
 * @psalm-pure
 *
 * @param numeric $to
 * @return ConstraintInterface<numeric>
 */
function greaterOrEqual(mixed $to): ConstraintInterface
{
    return new C\Numeric\ComparisonConstraint(
        type: C\Numeric\ComparisonConstraint::OP_GREATER_OR_EQUAL,
        value: $to,
    );
}

/**
 * @psalm-pure
 *
 * @param numeric $than
 * @return ConstraintInterface<numeric>
 */
function less(mixed $than): ConstraintInterface
{
    return new C\Numeric\ComparisonConstraint(
        type: C\Numeric\ComparisonConstraint::OP_LESS,
        value: $than,
    );
}

/**
 * @psalm-pure
 *
 * @param numeric $to
 * @return ConstraintInterface<numeric>
 */
function lessOrEqual(mixed $to): ConstraintInterface
{
    return new C\Numeric\ComparisonConstraint(
        type: C\Numeric\ComparisonConstraint::OP_LESS_OR_EQUAL,
        value: $to,
    );
}

/**
 * @psalm-pure
 *
 * @param numeric $from
 * @param numeric $to
 * @return ConstraintInterface<numeric>
 */
function inRange(mixed $from, mixed $to): ConstraintInterface
{
    return new C\Numeric\InRangeConstraint($from, $to);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<string>
 */
function minLength(int $is): ConstraintInterface
{
    return new C\String\MinLengthConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<string>
 */
function maxLength(int $is): ConstraintInterface
{
    return new C\String\MaxLengthConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $string
 * @return ConstraintInterface<string>
 */
function startsWith(string $string): ConstraintInterface
{
    return new C\String\StartsWithConstraint($string);
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $string
 * @return ConstraintInterface<string>
 */
function endsWith(string $string): ConstraintInterface
{
    return new C\String\EndsWithConstraint($string);
}

/**
 * @psalm-pure
 *
 * @return ConstraintInterface<string>
 */
function uuid(): ConstraintInterface
{
    return new C\String\UuidConstraint();
}

/**
 * @psalm-pure
 *
 * @return ConstraintInterface<string>
 */
function trimmed(): ConstraintInterface
{
    return new C\String\TrimmedConstraint();
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $regex
 * @return ConstraintInterface<string>
 */
function matchesRegex(string $regex): ConstraintInterface
{
    return new C\String\MatchesRegexConstraint($regex);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param ConstraintInterface<T> $constraint
 * @return ConstraintInterface<array<array-key, T>>
 */
function forall(ConstraintInterface $constraint): ConstraintInterface
{
    return new C\Collection\ForallConstraint($constraint);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param ConstraintInterface<T> $constraint
 * @return ConstraintInterface<array<array-key, T>>
 */
function exists(ConstraintInterface $constraint): ConstraintInterface
{
    return new C\Collection\ExistsConstraint($constraint);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param T $value
 * @return ConstraintInterface<array<array-key, T>>
 */
function inCollection(mixed $value): ConstraintInterface
{
    return new C\Collection\InCollectionConstraint($value);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<array>
 */
function maxSize(int $is): ConstraintInterface
{
    return new C\Collection\MaxSizeConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<array>
 */
function minSize(int $is): ConstraintInterface
{
    return new C\Collection\MinSizeConstraint($is);
}

/**
 * @template T
 * @psalm-pure
 * @no-named-arguments
 *
 * @param ConstraintInterface<T> $head
 * @param ConstraintInterface<T> ...$tail
 * @return ConstraintInterface<T>
 */
function allOf(ConstraintInterface $head, ConstraintInterface ...$tail): ConstraintInterface
{
    return new C\Boolean\AllOfConstraint([$head, ...$tail]);
}

/**
 * @template T
 * @psalm-pure
 * @no-named-arguments
 *
 * @param ConstraintInterface<T> $head
 * @param ConstraintInterface<T> ...$tail
 * @return ConstraintInterface<T>
 */
function anyOf(ConstraintInterface $head, ConstraintInterface ...$tail): ConstraintInterface
{
    return new C\Boolean\AnyOfConstraint([$head, ...$tail]);
}

/**
 * @template T
 * @psalm-pure
 * @no-named-arguments
 *
 * @param ConstraintInterface<T> $constraint
 * @return ConstraintInterface<T>
 */
function not(ConstraintInterface $constraint): ConstraintInterface
{
    return new C\Boolean\NotConstraint($constraint);
}
