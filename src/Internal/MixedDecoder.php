<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder;
use function Klimick\Decode\valid;

/**
 * @extends Decoder<mixed>
 * @psalm-immutable
 */
final class MixedDecoder extends Decoder
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
