<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Klimick\Decode\Test\Helper\AssertType;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\scalar;
use function Klimick\Decode\Test\Helper\anyValue;

final class ScalarDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), scalar());
        AssertType::scalar($decoded);
    }
}
