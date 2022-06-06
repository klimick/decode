<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

/**
 * @extends AbstractDecoder<null>
 * @psalm-immutable
 */
final class NullDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'null';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return null === $value ? valid($value) : invalid($context);
    }
}
