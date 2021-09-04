<?php

namespace Klimick\Decode\Internal;

use Fp\Collections\NonEmptyArrayList;
use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T of scalar
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class LiteralDecoder extends AbstractDecoder
{
    /**
     * @param NonEmptyArrayList<T> $literals
     */
    public function __construct(public NonEmptyArrayList $literals) { }

    public function name(): string
    {
        $literals = $this->literals
            ->map(fn($literal) => match(true) {
                is_string($literal) => "'$literal'",
                is_bool($literal) => $literal ? 'true' : 'false',
                default => (string) $literal,
            })
            ->toArray();

        return implode(' | ', $literals);
    }

    public function is(mixed $value): bool
    {
        return $this->literals->exists(fn($literal) => $literal === $value);
    }

    public function decode(mixed $value, Context $context): Either
    {
        foreach ($this->literals as $literal) {
            if ($value === $literal) {
                return valid($literal);
            }
        }

        return invalid($context);
    }
}
