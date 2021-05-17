<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\DecoderInterface;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @implements DecoderInterface<null>
 * @psalm-immutable
 */
final class NullDecoder implements DecoderInterface
{
    public function name(): string
    {
        return 'null';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return null === $value ? valid($value) : invalid($context);
    }
}
