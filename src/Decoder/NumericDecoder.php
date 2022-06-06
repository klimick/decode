<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

/**
 * @extends AbstractDecoder<numeric>
 * @psalm-immutable
 */
final class NumericDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'numeric';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_numeric($value) ? valid($value) : invalid($context);
    }
}
