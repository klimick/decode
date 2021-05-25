<?php

declare(strict_types=1);

namespace Klimick\Decode\Constraint;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

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
 * @param non-empty-string $constraint
 * @return Either<Invalid, empty>
 */
function invalid(Context $context, string $constraint, array $payload = []): Either
{
    return invalids([
        new ConstraintError($context, $constraint, $payload)
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
 * @template TNumeric of numeric
 *
 * @param TNumeric $than
 * @return Gt<TNumeric>
 * @psalm-pure
 */
function greater(mixed $than): Gt
{
    throw new \RuntimeException('???');
}

/**
 * @template TNumeric of numeric
 *
 * @param TNumeric $to
 * @return Gte<TNumeric>
 * @psalm-pure
 */
function greaterOrEqual(mixed $to): Gte
{
    throw new \RuntimeException('???');
}

/**
 * @template TNumeric of numeric
 *
 * @param TNumeric $than
 * @return Lt<TNumeric>
 * @psalm-pure
 */
function less(mixed $than): Lt
{
    throw new \RuntimeException('???');
}

/**
 * @template TNumeric of numeric
 *
 * @param TNumeric $to
 * @return Lte<TNumeric>
 * @psalm-pure
 */
function lessOrEqual(mixed $to): Lte
{
    throw new \RuntimeException('???');
}

/**
 * @return ConstraintInterface<numeric>
 * @psalm-pure
 */
function span(mixed $from, mixed $to): ConstraintInterface
{
    throw new \RuntimeException('???');
}
