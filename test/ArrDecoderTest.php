<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\t;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\arr;
use function Klimick\Decode\Test\Helper\forAll;

final class ArrDecoderTest extends TestCase
{
    public function testValidForAllArraysWithIntKey(): void
    {
        [$arrItemDecoder, $arrItemGen] = DecoderGenerator::generate();

        $arrGen = Gen::oneOf(
            Gen::arr(Gen::int(), $arrItemGen),
            Gen::nonEmptyArr(Gen::int(), $arrItemGen),
            Gen::arrList($arrItemGen),
            Gen::nonEmptyArrList($arrItemGen),
        );

        forAll($arrGen)
            ->withMaxSize(50)
            ->then(
                Check::thatValidFor(arr(t::int, $arrItemDecoder))
            );
    }

    public function testValidForAllArraysWithStringKey(): void
    {
        [$arrItemDecoder, $arrItemGen] = DecoderGenerator::generate();

        $arrGen = Gen::oneOf(
            Gen::arr(Gen::arrKey('string'), $arrItemGen),
            Gen::nonEmptyArr(Gen::arrKey('string'), $arrItemGen),
        );

        forAll($arrGen)
            ->withMaxSize(50)
            ->then(
                Check::thatValidFor(arr(t::string, $arrItemDecoder))
            );
    }

    public function testInvalidForAllNotArrays(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $v) => !is_array($v))
            ->then(
                Check::thatInvalidFor(arr(t::arrKey, t::mixed))
            );
    }
}
