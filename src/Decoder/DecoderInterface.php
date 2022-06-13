<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use Fp\Functional\Either\Either;
use Fp\Functional\Option\Option;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Error\Context;
use Klimick\Decode\Error\DecodeError;
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
     * @param Context<DecoderInterface> $context
     * @return Either<non-empty-list<DecodeError>, T>
     */
    public function decode(mixed $value, Context $context): Either;

    /**
     * @template ContravariantT
     * @no-named-arguments
     *
     * @param ConstraintInterface<ContravariantT> $head
     * @param ConstraintInterface<ContravariantT> ...$tail
     * @return DecoderInterface<T>
     *
     * @see ConstrainedMethodReturnTypeProvider Contravariant check happens via plugin
     */
    public function constrained(ConstraintInterface $head, ConstraintInterface ...$tail): DecoderInterface;

    /**
     * @return DecoderInterface<T> & object{possiblyUndefined: true}
     */
    public function orUndefined(): DecoderInterface;

    public function isPossiblyUndefined(): bool;

    /**
     * @param non-empty-string $head
     * @param non-empty-string ...$tail
     * @return DecoderInterface<T>
     *
     * @no-named-arguments
     */
    public function from(string $head, string ...$tail): DecoderInterface;

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
