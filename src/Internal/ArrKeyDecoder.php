<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

/**
 * @extends AbstractDecoder<array-key>
 * @psalm-immutable
 */
final class ArrKeyDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'array-key';
    }

    /**
     * @psalm-assert-if-true array-key $value
     */
    public function is(mixed $value): bool
    {
        return is_int($value) || is_string($value);
    }

    public function decode(mixed $value, Context $context): Either
    {
        if (!$this->is($value)) {
            return invalid($context);
        }

        return valid($value);
    }
}
