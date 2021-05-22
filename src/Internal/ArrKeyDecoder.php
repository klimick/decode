<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @extends Decoder<array-key>
 * @psalm-immutable
 */
final class ArrKeyDecoder extends Decoder
{
    public function name(): string
    {
        return 'array-key';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_int($value) || is_string($value) ? valid($value) : invalid($context);
    }
}
