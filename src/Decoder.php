<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Fp\Functional\Either\Either;
use Klimick\Decode\Internal\Constraint\ConstraintInterface;
use Klimick\Decode\Internal\HighOrder\ConstrainedDecoder;

/**
 * @template-covariant T
 * @psalm-immutable
 */
abstract class Decoder
{
    /**
     * @return non-empty-string
     */
    abstract public function name(): string;

    /**
     * @return Either<Invalid, Valid<T>>
     */
    abstract public function decode(mixed $value, Context $context): Either;

    /**
     * @template ContravariantT
     *
     * @param non-empty-list<ConstraintInterface<ContravariantT>> $constraints
     * @return ConstrainedDecoder<T>
     */
    public function constrained(array $constraints): ConstrainedDecoder
    {
        return new ConstrainedDecoder($this, $constraints);
    }
}
