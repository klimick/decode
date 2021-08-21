<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Klimick\Decode\Test\Helper\AssertType;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\positiveInt;
use function Klimick\Decode\Test\Helper\anyValue;

final class PositiveIntDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), positiveInt());

        AssertType::int($decoded);
        AssertType::positiveInt($decoded);
    }
}
