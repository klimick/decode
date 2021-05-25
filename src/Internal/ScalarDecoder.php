<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

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
