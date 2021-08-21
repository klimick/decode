<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Klimick\Decode\Test\Helper\AssertType;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Test\Helper\anyValue;

final class NullDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), null());
        AssertType::null($decoded);

        /** @psalm-suppress InvalidArgument */
        AssertType::string($decoded);
    }
}
