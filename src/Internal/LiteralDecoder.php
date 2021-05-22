<?php

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @template T of scalar
 * @extends Decoder<T>
 * @psalm-immutable
 */
final class LiteralDecoder extends Decoder
{
    /**
     * @param non-empty-list<T> $literals
     */
    public function __construct(public array $literals) {}

    public function name(): string
    {
        return implode(' | ', array_map(
            callback: fn(mixed $v) => is_string($v) ? "'$v'" : (string) $v,
            array: $this->literals
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

    public function encode(mixed $value): mixed
    {
        return $value;
    }
}
