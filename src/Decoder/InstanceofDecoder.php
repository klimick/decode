<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class InstanceofDecoder extends AbstractDecoder
{
    /**
     * @param class-string<T> $class
     */
    public function __construct(public string $class)
    {
    }

    public function name(): string
    {
        return $this->class;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $value instanceof $this->class ? valid($value) : invalid($context);
    }
}
