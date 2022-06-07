<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class ConstantlyDecoder extends AbstractDecoder
{
    /**
     * @param T $constant
     */
    public function __construct(public mixed $constant) { }

    public function name(): string
    {
        return 'constant';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return valid($this->constant);
    }
}
