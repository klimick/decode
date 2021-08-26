<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\invalid;
use function Klimick\Decode\Decoder\valid;

/**
 * @extends AbstractDecoder<bool>
 * @psalm-immutable
 */
final class BoolDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'bool';
    }

    /**
     * @psalm-assert-if-true bool $value
     */
    public function is(mixed $value): bool
    {
        return is_bool($value);
    }

    public function decode(mixed $value, Context $context): Either
    {
        return $this->is($value) ? valid($value) : invalid($context);
    }
}
