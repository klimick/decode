<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

/**
 * @extends AbstractDecoder<float>
 * @psalm-immutable
 */
final class FloatDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'float';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_float($value) ? valid($value) : invalid($context);
    }
}
