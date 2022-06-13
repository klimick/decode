<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;

/**
 * @extends AbstractDecoder<array-key>
 * @psalm-immutable
 */
final class ArrayKeyDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'array-key';
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!is_int($value) && !is_string($value)) {
            return invalid($context);
        }

        return valid($value);
    }
}
