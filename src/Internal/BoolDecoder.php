<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @extends Decoder<bool>
 * @psalm-immutable
 */
final class BoolDecoder extends Decoder
{
    public function name(): string
    {
        return 'bool';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_bool($value) ? valid($value) : invalid($context);
    }
}
