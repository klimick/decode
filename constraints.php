<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Klimick\Decode\Context;
use Klimick\Decode\Internal\Constraint\ComparisonConstraint;
use Klimick\Decode\Internal\Constraint\ForAll;
use Klimick\Decode\Internal\Constraint\InRangeConstraint;
use Klimick\Decode\Internal\Constraint\MaxLengthConstraint;
use Klimick\Decode\Internal\Constraint\MinLengthConstraint;
use Fp\Functional\Either\Either;

/**
 * @psalm-pure
 *
 * @return Either<empty, Valid>
 */
function valid(): Either
{
    /** @psalm-suppress ImpureMethodCall */
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
    /** @psalm-suppress ImpureMethodCall */
    return Either::left(new Invalid($errors));
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
 * @param numeric $to
 * @return ConstraintInterface<numeric>
 */
function equal(mixed $to): ConstraintInterface
{
    return new ComparisonConstraint(
        type: ComparisonConstraint::OP_EQUAL,
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
 * @param positive-int $is
 * @return ConstraintInterface<string>
 */
function minLength(int $is): ConstraintInterface
{
    return new MinLengthConstraint($is);
}

/**
 * @param positive-int $is
 * @return ConstraintInterface<string>
 */
function maxLength(int $is): ConstraintInterface
{
    return new MaxLengthConstraint($is);
}

/**
 * @template T
 * @psalm-pure
 *
 * @param non-empty-list<ConstraintInterface<T>> $constraints
 * @return ConstraintInterface<list<T>>
 */
function forAll(array $constraints): ConstraintInterface
{
    return new ForAll($constraints);
}
