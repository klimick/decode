<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @extends Decoder<int>
 * @psalm-immutable
 */
final class IntDecoder extends Decoder
{
    public function name(): string
    {
        return 'int';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_int($value) ? valid($value) : invalid($context);
    }
}
