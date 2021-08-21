<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\union;
use function Klimick\Decode\Test\Helper\anyValue;

final class UnionDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), union(
            int(),
            string(),
            bool()
        ));

        self::assertTypeUnion($decoded);
    }

    /**
     * @param Option<int | string | bool> $_param
     */
    private static function assertTypeUnion(Option $_param): void
    {
    }
}
