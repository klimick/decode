<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class ConstantDecoder extends AbstractDecoder
{
    /**
     * @param T $constant
     */
    public function __construct(public mixed $constant) { }

    public function name(): string
    {
        return 'constant';
    }

    public function is(mixed $value): bool
    {
        return $value === $this->constant;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return valid($this->constant);
    }
}
