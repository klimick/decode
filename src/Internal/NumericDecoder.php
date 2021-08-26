<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

/**
 * @extends AbstractDecoder<numeric>
 * @psalm-immutable
 */
final class NumericDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'numeric';
    }

    /**
     * @psalm-assert-if-true numeric $value
     */
    public function is(mixed $value): bool
    {
        return is_numeric($value);
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->is($value) ? valid($value) : invalid($context);
    }
}
