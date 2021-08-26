<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Internal\HighOrder\FromDecoder;
use Klimick\Decode\Internal\HighOrder\DefaultDecoder;
use Klimick\Decode\Internal\HighOrder\OptionalDecoder;
use Klimick\Decode\Internal\HighOrder\ConstrainedDecoder;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\PsalmDecode\HighOrder\ConstrainedContravariantCheckHandler;

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
     * @psalm-assert-if-true T $value
     */
    abstract public function is(mixed $value): bool;

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
        return new ConstrainedDecoder([$first, ...$rest], $this);
    }

    /**
     * @return AbstractDecoder<T>
     */
    public function optional(): AbstractDecoder
    {
        return new OptionalDecoder($this);
    }

    /**
     * @param non-empty-string $with
     * @return AbstractDecoder<T>
     */
    public function from(string $with): AbstractDecoder
    {
        return new FromDecoder($with, $this);
    }

    /**
     * @param T $value
     * @return AbstractDecoder<T>
     */
    public function default(mixed $value): AbstractDecoder
    {
        return new DefaultDecoder($value, $this);
    }
}
