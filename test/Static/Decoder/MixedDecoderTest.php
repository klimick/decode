<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Klimick\Decode\Test\Helper\AssertType;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Test\Helper\anyValue;

final class MixedDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), mixed());

        AssertType::mixed($decoded);

        /** @psalm-suppress MixedArgumentTypeCoercion */
        AssertType::string($decoded);
    }
}
