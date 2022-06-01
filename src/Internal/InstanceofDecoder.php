<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

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
