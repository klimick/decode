<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\Error\DecodeErrorInterface;
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
     * @return Either<non-empty-list<DecodeErrorInterface>, T>
     */
    public function decode(mixed $value, Context $context): Either;

    /**
     * @template ContravariantT
     * @no-named-arguments
     *
     * @param ConstraintInterface<ContravariantT> $first
     * @param ConstraintInterface<ContravariantT> ...$rest
     * @return DecoderInterface<T>
     *
     * @see ConstrainedMethodReturnTypeProvider Contravariant check happens via plugin
     */
    public function constrained(ConstraintInterface $first, ConstraintInterface ...$rest): DecoderInterface;

    /**
     * @return DecoderInterface<T> & object{possiblyUndefined: true}
     */
    public function orUndefined(): DecoderInterface;

    public function isPossiblyUndefined(): bool;

    /**
     * @param non-empty-string $alias
     * @param non-empty-string ...$rest
     * @return DecoderInterface<T>
     *
     * @no-named-arguments
     */
    public function from(string $alias, string ...$rest): DecoderInterface;

    /**
     * @return list<non-empty-string>
     */
    public function getAliases(): array;

    /**
     * @param T $value
     * @return DecoderInterface<T>
     */
    public function default(mixed $value): DecoderInterface;

    /**
     * @return Option<T>
     */
    public function getDefault(): Option;

    /**
     * @template TMapped
     *
     * @param Closure(T): TMapped $to
     * @return DecoderInterface<TMapped>
     */
    public function map(Closure $to): DecoderInterface;
}
