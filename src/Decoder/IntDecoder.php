<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;

/**
 * @extends AbstractDecoder<int>
 * @psalm-immutable
 */
final class IntDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'int';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_int($value) ? valid($value) : invalid($context);
    }
}
