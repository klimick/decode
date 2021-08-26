<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

/**
 * @extends AbstractDecoder<positive-int>
 * @psalm-immutable
 */
final class PositiveIntDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'positive-int';
    }

    /**
     * @psalm-assert-if-true positive-int $value
     */
    public function is(mixed $value): bool
    {
        return is_int($value) && $value > 0;
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->is($value) ? valid($value) : invalid($context);
    }
}
