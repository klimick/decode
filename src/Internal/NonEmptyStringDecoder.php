<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

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

    /**
     * @psalm-assert-if-true non-empty-string $value
     */
    public function is(mixed $value): bool
    {
        return is_string($value) && $value !== '';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->is($value) ? valid($value) : invalid($context);
    }
}
