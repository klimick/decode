<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Internal\HighOrder\ConstrainedDecoder;
use Klimick\PsalmDecode\Constraint\ConstrainedContravariantCheckHandler;

/**
 * @template-covariant T
 * @psalm-immutable
 */
abstract class AbstractDecoder
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
     * @no-named-arguments
     *
     * @param ConstraintInterface<ContravariantT> $first
     * @param ConstraintInterface<ContravariantT> ...$rest
     * @return AbstractDecoder<T>
     *
     * @see ConstrainedContravariantCheckHandler Contravariant check happens via plugin
     */
    public function constrained(ConstraintInterface $first, ConstraintInterface ...$rest): AbstractDecoder
    {
        return new ConstrainedDecoder($this, [$first, ...$rest]);
    }
}
