<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder;
use function Klimick\Decode\invalid;
use function Klimick\Decode\valid;

/**
 * @extends Decoder<non-empty-string>
 * @psalm-immutable
 */
final class NonEmptyStringDecoder extends Decoder
{
    public function name(): string
    {
        return 'non-empty-string';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_string($value) && '' !== $value ? valid($value) : invalid($context);
    }
}
