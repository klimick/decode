<?php

declare(strict_types=1);

namespace Klimick\Decode\Internal;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\AbstractDecoder;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\union;

/**
 * @extends AbstractDecoder<array-key>
 * @psalm-immutable
 */
final class ArrKeyDecoder extends AbstractDecoder
{
    public function name(): string
    {
        return 'array-key';
    }

    public function decode(mixed $value, Context $context): Either
    {
        return union(int(), string())->decode($value, $context);
    }
}
