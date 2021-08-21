<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\literal;
use function Klimick\Decode\Test\Helper\anyValue;

final class LiteralDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), literal('1', '2', '3'));
        self::assertTypeLiteral($decoded);
    }

    /**
     * @param Option<'1' | '2' | '3'> $_param
     */
    private static function assertTypeLiteral(Option $_param): void
    {
    }
}
