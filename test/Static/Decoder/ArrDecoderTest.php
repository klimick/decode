<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\arr;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Test\Helper\anyValue;

final class ArrDecoderTest
{
    public function test(): void
    {
        self::assertTypeListString(cast(
            anyValue(),
            arr(int(), string())
        ));
        self::assertTypeListInt(cast(
            anyValue(),
            arr(int(), int())
        ));
        self::assertTypeListBool(cast(
            anyValue(),
            arr(int(), bool())
        ));
    }

    /**
     * @param Option<array<int, string>> $_param
     */
    private static function assertTypeListString(Option $_param): void
    {
    }

    /**
     * @param Option<array<int, int>> $_param
     */
    private static function assertTypeListInt(Option $_param): void
    {
    }

    /**
     * @param Option<array<int, bool>> $_param
     */
    private static function assertTypeListBool(Option $_param): void
    {
    }
}
