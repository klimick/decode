<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @implements DecoderInterface<int>
 * @psalm-immutable
 */
final class IntDecoder implements DecoderInterface
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
