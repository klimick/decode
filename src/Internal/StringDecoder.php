<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @implements DecoderInterface<string>
 * @psalm-immutable
 */
final class StringDecoder implements DecoderInterface
{
    public function name(): string
    {
        return 'string';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_string($value) ? valid($value) : invalid($context);
    }
}
