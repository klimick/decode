<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\nonEmptyArr;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Test\Helper\anyValue;

final class NonEmptyArrDecoderTest
{
    public function test(): void
    {
        self::assertTypeListString(cast(
            anyValue(),
            nonEmptyArr(int(), string())
        ));
        self::assertTypeListInt(cast(
            anyValue(),
            nonEmptyArr(int(), int())
        ));
        self::assertTypeListBool(cast(
            anyValue(),
            nonEmptyArr(int(), bool())
        ));
    }

    /**
     * @param Option<non-empty-array<int, string>> $_param
     */
    private static function assertTypeListString(Option $_param): void
    {
    }

    /**
     * @param Option<non-empty-array<int, int>> $_param
     */
    private static function assertTypeListInt(Option $_param): void
    {
    }

    /**
     * @param Option<non-empty-array<int, bool>> $_param
     */
    private static function assertTypeListBool(Option $_param): void
    {
    }
}
