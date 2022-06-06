<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Closure;
use Klimick\Decode\Constraint\ConstraintInterface;
use Klimick\Decode\Decoder\HighOrder\ConstrainedDecoder;
use Klimick\Decode\Decoder\HighOrder\DefaultDecoder;
use Klimick\Decode\Decoder\HighOrder\FromDecoder;
use Klimick\Decode\Decoder\HighOrder\OptionalDecoder;

/**
 * @template-covariant T
 * @implements DecoderInterface<T>
 * @psalm-immutable
 */
abstract class AbstractDecoder implements DecoderInterface
{
    /**
     * @no-named-arguments
     */
    public function constrained(ConstraintInterface $first, ConstraintInterface ...$rest): DecoderInterface
    {
        return new ConstrainedDecoder([$first, ...$rest], $this);
    }

    public function optional(): DecoderInterface
    {
        return new OptionalDecoder($this);
    }

    public function from(string $with): DecoderInterface
    {
        return new FromDecoder($with, $this);
    }

    public function default(mixed $value): DecoderInterface
    {
        return new DefaultDecoder($value, $this);
    }

    public function map(Closure $to): DecoderInterface
    {
        return new MapDecoder($this, $to);
    }
}
