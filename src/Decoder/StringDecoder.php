<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

/**
 * @extends AbstractDecoder<string>
 * @psalm-immutable
 */
final class StringDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'string';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_string($value) ? valid($value) : invalid($context);
    }
}
