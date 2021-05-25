<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\valid;

/**
 * @template T
 * @extends AbstractDecoder<T>
 * @psalm-immutable
 */
final class FallbackDecoder extends AbstractDecoder
{
    /**
     * @param T $fallback
     */
    public function __construct(public mixed $fallback) { }

    public function name(): string
    {
        return 'fallback';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return valid($this->fallback);
    }
}
