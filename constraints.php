<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;

/**
 * @psalm-pure
 */
function invalid(Context $context, ConstraintInterface $constraint): ConstraintError
{
    return new ConstraintError($context, $constraint->name(), $constraint->payload());
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
    return new \Klimick\Decode\Constraint\EqualConstraint($to);
}

/**
 * @psalm-pure
 *
 * @param numeric $than
 * @return ConstraintInterface<numeric>
 */
function greater(mixed $than): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\ComparisonConstraint(
        type: \Klimick\Decode\Constraint\ComparisonConstraint::OP_GREATER,
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
    return new \Klimick\Decode\Constraint\ComparisonConstraint(
        type: \Klimick\Decode\Constraint\ComparisonConstraint::OP_GREATER_OR_EQUAL,
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
    return new \Klimick\Decode\Constraint\ComparisonConstraint(
        type: \Klimick\Decode\Constraint\ComparisonConstraint::OP_LESS,
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
    return new \Klimick\Decode\Constraint\ComparisonConstraint(
        type: \Klimick\Decode\Constraint\ComparisonConstraint::OP_LESS_OR_EQUAL,
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
    return new \Klimick\Decode\Constraint\InRangeConstraint($from, $to);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<string>
 */
function minLength(int $is): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\MinLengthConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<string>
 */
function maxLength(int $is): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\MaxLengthConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $string
 * @return ConstraintInterface<string>
 */
function startsWith(string $string): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\StartsWithConstraint($string);
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $string
 * @return ConstraintInterface<string>
 */
function endsWith(string $string): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\EndsWithConstraint($string);
}

/**
 * @psalm-pure
 *
 * @return ConstraintInterface<string>
 */
function uuid(): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\UuidConstraint();
}

/**
 * @psalm-pure
 *
 * @return ConstraintInterface<string>
 */
function trimmed(): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\TrimmedConstraint();
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $regex
 * @return ConstraintInterface<string>
 */
function matchesRegex(string $regex): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\MatchesRegexConstraint($regex);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param ConstraintInterface<T> $constraint
 * @return ConstraintInterface<array<array-key, T>>
 */
function every(ConstraintInterface $constraint): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\EveryConstraint($constraint);
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
    return new \Klimick\Decode\Constraint\ExistsConstraint($constraint);
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
    return new \Klimick\Decode\Constraint\InCollectionConstraint($value);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<array>
 */
function maxSize(int $is): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\MaxSizeConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<array>
 */
function minSize(int $is): ConstraintInterface
{
    return new \Klimick\Decode\Constraint\MinSizeConstraint($is);
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
    return new \Klimick\Decode\Constraint\AllOfConstraint([$head, ...$tail]);
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
    return new \Klimick\Decode\Constraint\AnyOfConstraint([$head, ...$tail]);
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
    return new \Klimick\Decode\Constraint\NotConstraint($constraint);
}
