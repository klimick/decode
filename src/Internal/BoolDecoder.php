<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @implements DecoderInterface<bool>
 * @psalm-immutable
 */
final class BoolDecoder implements DecoderInterface
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
