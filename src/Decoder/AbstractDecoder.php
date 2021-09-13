<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Klimick\Decode\Internal\HighOrder\FromDecoder;
use Klimick\Decode\Internal\HighOrder\DefaultDecoder;
use Klimick\Decode\Internal\HighOrder\OptionalDecoder;
use Klimick\Decode\Internal\HighOrder\ConstrainedDecoder;
use Klimick\Decode\Constraint\ConstraintInterface;

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
}
