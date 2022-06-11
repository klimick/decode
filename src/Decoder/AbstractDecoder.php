<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use Fp\Functional\Option\Option;
use Klimick\Decode\Constraint\ConstraintInterface;

/**
 * @template-covariant T
 * @implements DecoderInterface<T>
 * @psalm-immutable
 */
abstract class AbstractDecoder implements DecoderInterface
{
    private bool $possiblyUndefined = false;

    /** @var T */
    private mixed $default;

    /** @var list<non-empty-string> */
    private array $aliases = [];

    /**
     * @return DecoderInterface<T> & object{possiblyUndefined: true}
     */
    public function orUndefined(): DecoderInterface
    {
        $self = clone $this;
        $self->possiblyUndefined = true;

        /** @var DecoderInterface<T> & object{possiblyUndefined: true} */
        return $self;
    }

    public function isPossiblyUndefined(): bool
    {
        return $this->possiblyUndefined;
    }

    /**
     * @param non-empty-string $head
     * @param non-empty-string ...$tail
     * @return DecoderInterface<T>
     *
     * @no-named-arguments
     */
    public function from(string $head, string ...$tail): DecoderInterface
    {
        $self = clone $this;
        $self->aliases = [$head, ...$tail];

        return $self;
    }

    /**
     * @return list<non-empty-string>
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param T $value
     * @return DecoderInterface<T>
     */
    public function default(mixed $value): DecoderInterface
    {
        $self = clone $this;
        $self->default = $value;

        return $self;
    }

    /**
     * @return Option<T>
     */
    final public function getDefault(): Option
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        return isset($this->default)
            ? Option::some($this->default)
            : Option::none();
    }

    /**
     * @template ContravariantT
     *
     * @param ConstraintInterface<ContravariantT> $head
     * @param ConstraintInterface<ContravariantT> ...$tail
     * @return DecoderInterface<T>
     *
     * @no-named-arguments
     */
    public function constrained(ConstraintInterface $head, ConstraintInterface ...$tail): DecoderInterface
    {
        return (new ConstrainedDecoder([$head, ...$tail], $this))->from(...$this->aliases);
    }

    /**
     * @template TMapped
     *
     * @param Closure(T): TMapped $to
     * @return DecoderInterface<TMapped>
     */
    public function map(Closure $to): DecoderInterface
    {
        return (new MapDecoder($this, $to))->from(...$this->aliases);
    }
}
