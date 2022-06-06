<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;

/**
 * @extends AbstractDecoder<scalar>
 * @psalm-immutable
 */
final class ScalarDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'scalar';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_scalar($value) ? valid($value) : invalid($context);
    }
}
