<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\tuple;
use function Klimick\Decode\Test\Helper\anyValue;

final class TupleDecoderTest
{
    public function test(): void
    {
        self::assertTypeTuple1(cast(
            anyValue(),
            tuple(string())
        ));

        self::assertTypeTuple2(cast(
            anyValue(),
            tuple(string(), bool())
        ));

        self::assertTypeTuple3(cast(
            anyValue(),
            tuple(string(), int(), int())
        ));
    }

    /**
     * @param Option<array{string}> $_param
     */
    private static function assertTypeTuple1(Option $_param): void
    {
    }

    /**
     * @param Option<array{string, bool}> $_param
     */
    private static function assertTypeTuple2(Option $_param): void
    {
    }

    /**
     * @param Option<array{string, int, int}> $_param
     */
    private static function assertTypeTuple3(Option $_param): void
    {
    }
}
