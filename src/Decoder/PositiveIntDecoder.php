<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;

/**
 * @extends AbstractDecoder<positive-int>
 * @psalm-immutable
 */
final class PositiveIntDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'positive-int';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_int($value) && $value > 0 ? valid($value) : invalid($context);
    }
}
