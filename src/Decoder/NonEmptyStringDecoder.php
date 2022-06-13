<?php

declare(strict_types=1);

namespace Klimick\Decode\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Error\Context;

/**
 * @extends AbstractDecoder<non-empty-string>
 * @psalm-immutable
 */
final class NonEmptyStringDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'non-empty-string';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return is_string($value) && $value !== '' ? valid($value) : invalid($context);
    }
}
