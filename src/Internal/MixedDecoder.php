<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\valid;

/**
 * @extends AbstractDecoder<mixed>
 * @psalm-immutable
 */
final class MixedDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'mixed';
    }

    public function is(mixed $value): bool
    {
        return true;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return valid($value);
    }
}
