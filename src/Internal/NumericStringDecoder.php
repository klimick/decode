<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @extends AbstractDecoder<numeric-string>
 * @psalm-immutable
 */
final class NumericStringDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'numeric-string';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_string($value) && is_numeric($value) ? valid($value) : invalid($context);
    }
}
