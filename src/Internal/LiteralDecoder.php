<?php

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Fp\Collection\exists;
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
     * @param non-empty-list<T> $literals
     */
    public function __construct(public array $literals) { }

    public function name(): string
    {
        return implode(' | ', array_map(
            fn($literal) => match(true) {
                is_string($literal) => "'$literal'",
                is_bool($literal) => $literal ? 'true' : 'false',
                default => (string) $literal,
            },
            $this->literals
        ));
    }

    public function is(mixed $value): bool
    {
        return exists($this->literals, fn(mixed $literal) => $literal === $value);
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
