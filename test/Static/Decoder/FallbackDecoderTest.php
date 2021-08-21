<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Klimick\Decode\Test\Helper\AssertType;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\fallback;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Test\Helper\anyValue;

final class FallbackDecoderTest
{
    public function test(): void
    {
        AssertType::int(cast(
            anyValue(), fallback(1),
        ));
        AssertType::string(cast(
            anyValue(), fallback('1'),
        ));
        AssertType::bool(cast(
            anyValue(), fallback(true),
        ));
    }
}
