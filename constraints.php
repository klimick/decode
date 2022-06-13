<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Error\ConstraintError;
use Klimick\Decode\Error\Context;

/**
 * @param Context<ConstraintInterface> $context
 * @psalm-pure
 */
function invalid(Context $context): ConstraintError
{
    return new ConstraintError($context);
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
    return new EqualConstraint($to);
}

/**
 * @psalm-pure
 *
 * @param numeric $than
 * @return ConstraintInterface<numeric>
 */
function greater(mixed $than): ConstraintInterface
{
    return new ComparisonConstraint(
        type: ComparisonConstraint::OP_GREATER,
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
    return new ComparisonConstraint(
        type: ComparisonConstraint::OP_GREATER_OR_EQUAL,
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
    return new ComparisonConstraint(
        type: ComparisonConstraint::OP_LESS,
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
    return new ComparisonConstraint(
        type: ComparisonConstraint::OP_LESS_OR_EQUAL,
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
    return new InRangeConstraint($from, $to);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<string>
 */
function minLength(int $is): ConstraintInterface
{
    return new MinLengthConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<string>
 */
function maxLength(int $is): ConstraintInterface
{
    return new MaxLengthConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $string
 * @return ConstraintInterface<string>
 */
function startsWith(string $string): ConstraintInterface
{
    return new StartsWithConstraint($string);
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $string
 * @return ConstraintInterface<string>
 */
function endsWith(string $string): ConstraintInterface
{
    return new EndsWithConstraint($string);
}

/**
 * @psalm-pure
 *
 * @return ConstraintInterface<string>
 */
function uuid(): ConstraintInterface
{
    return new UuidConstraint();
}

/**
 * @psalm-pure
 *
 * @return ConstraintInterface<string>
 */
function trimmed(): ConstraintInterface
{
    return new TrimmedConstraint();
}

/**
 * @psalm-pure
 *
 * @param non-empty-string $regex
 * @return ConstraintInterface<string>
 */
function matchesRegex(string $regex): ConstraintInterface
{
    return new MatchesRegexConstraint($regex);
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
    return new EveryConstraint($constraint);
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
    return new ExistsConstraint($constraint);
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
    return new InCollectionConstraint($value);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<array>
 */
function maxSize(int $is): ConstraintInterface
{
    return new MaxSizeConstraint($is);
}

/**
 * @psalm-pure
 *
 * @param positive-int $is
 * @return ConstraintInterface<array>
 */
function minSize(int $is): ConstraintInterface
{
    return new MinSizeConstraint($is);
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
function andX(ConstraintInterface $head, ConstraintInterface ...$tail): ConstraintInterface
{
    return new AndConstraint([$head, ...$tail]);
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
function orX(ConstraintInterface $head, ConstraintInterface ...$tail): ConstraintInterface
{
    return new OrConstraint([$head, ...$tail]);
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
    return new NotConstraint($constraint);
}
