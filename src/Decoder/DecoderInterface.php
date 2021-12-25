<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use Klimick\PsalmDecode\Hook\MethodReturnTypeProvider\ConstrainedMethodReturnTypeProvider;

/**
 * @template-covariant T
 * @psalm-immutable
 */
interface DecoderInterface
{
    /**
     * @return non-empty-string
     */
    public function name(): string;

    /**
     * @return Either<Invalid, Valid<T>>
     */
    public function decode(mixed $value, Context $context): Either;

    /**
     * @psalm-assert-if-true T $value
     */
    public function is(mixed $value): bool;

    /**
     * @template ContravariantT
     * @no-named-arguments
     *
     * @param ConstraintInterface<ContravariantT> $first
     * @param ConstraintInterface<ContravariantT> ...$rest
     * @return DecoderInterface<T>
     * @psalm-mutation-free
     *
     * @see ConstrainedMethodReturnTypeProvider Contravariant check happens via plugin
     */
    public function constrained(ConstraintInterface $first, ConstraintInterface ...$rest): DecoderInterface;

    /**
     * @return DecoderInterface<T>
     * @psalm-mutation-free
     */
    public function optional(): DecoderInterface;

    /**
     * @param non-empty-string $with
     * @return DecoderInterface<T>
     * @psalm-mutation-free
     */
    public function from(string $with): DecoderInterface;

    /**
     * @param T $value
     * @return DecoderInterface<T>
     * @psalm-mutation-free
     */
    public function default(mixed $value): DecoderInterface;
}
