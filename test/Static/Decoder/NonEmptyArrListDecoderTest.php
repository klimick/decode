<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\nonEmptyArrList;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Test\Helper\anyValue;

final class NonEmptyArrListDecoderTest
{
    public function test(): void
    {
        self::assertTypeListString(cast(
            anyValue(),
            nonEmptyArrList(string())
        ));
        self::assertTypeListInt(cast(
            anyValue(),
            nonEmptyArrList(int())
        ));
        self::assertTypeListBool(cast(
            anyValue(),
            nonEmptyArrList(bool())
        ));
    }

    /**
     * @param Option<non-empty-list<string>> $_param
     */
    private static function assertTypeListString(Option $_param): void
    {
    }

    /**
     * @param Option<non-empty-list<int>> $_param
     */
    private static function assertTypeListInt(Option $_param): void
    {
    }

    /**
     * @param Option<non-empty-list<bool>> $_param
     */
    private static function assertTypeListBool(Option $_param): void
    {
    }
}
