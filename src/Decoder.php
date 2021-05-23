<?php

declare(strict_types=1);

namespace Klimick\Decode;

use Fp\Functional\Either\Either;
use Klimick\Decode\Internal\Constraint\ConstraintInterface;
use Klimick\Decode\Internal\HighOrder\ConstrainedDecoder;
use Klimick\PsalmDecode\Constrain\ConstrainedContravariantCheckHandler;

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
     * @return Decoder<T>
     *
     * @see ConstrainedContravariantCheckHandler Contravariant check happens via plugin
     */
    public function constrained(array $constraints): Decoder
    {
        return new ConstrainedDecoder($this, $constraints);
    }
}
