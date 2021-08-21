<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Klimick\Decode\Test\Helper\AssertType;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\datetime;
use function Klimick\Decode\Test\Helper\anyValue;

final class DatetimeDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), datetime());
        AssertType::datetime($decoded);
    }
}
