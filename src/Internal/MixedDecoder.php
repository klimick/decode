<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\valid;

/**
 * @implements DecoderInterface<mixed>
 * @psalm-immutable
 */
final class MixedDecoder implements DecoderInterface
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
