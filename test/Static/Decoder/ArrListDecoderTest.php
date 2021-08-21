<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\arrList;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Test\Helper\anyValue;

final class ArrListDecoderTest
{
    public function test(): void
    {
        self::assertTypeListString(cast(
            anyValue(),
            arrList(string())
        ));
        self::assertTypeListInt(cast(
            anyValue(),
            arrList(int())
        ));
        self::assertTypeListBool(cast(
            anyValue(),
            arrList(bool())
        ));
    }

    public static function testW(): void
    {}

    /**
     * @param Option<list<string>> $_param
     */
    private static function assertTypeListString(Option $_param): void
    {
    }

    /**
     * @param Option<list<int>> $_param
     */
    private static function assertTypeListInt(Option $_param): void
    {
    }

    /**
     * @param Option<list<bool>> $_param
     */
    private static function assertTypeListBool(Option $_param): void
    {
    }
}
