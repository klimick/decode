<?php

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;
use function Fp\Collection\map;

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
        return implode(' | ', map(
            $this->literals,
            fn($literal) => match(true) {
                is_string($literal) => "'$literal'",
                is_bool($literal) => $literal ? 'true' : 'false',
                default => (string) $literal,
            },
        ));
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
