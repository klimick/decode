<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @implements DecoderInterface<float>
 * @psalm-immutable
 */
final class FloatDecoder implements DecoderInterface
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
