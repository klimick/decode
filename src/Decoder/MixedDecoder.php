<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

/**
 * @extends AbstractDecoder<mixed>
 * @psalm-immutable
 */
final class MixedDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'mixed';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return valid($value);
    }
}
