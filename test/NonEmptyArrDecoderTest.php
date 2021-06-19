<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Helper\Predicate;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\arrKey;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Decoder\nonEmptyArr;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Test\Helper\forAll;

final class NonEmptyArrDecoderTest extends TestCase
{
    public function testValidForAllArraysWithIntKey(): void
    {
        [$arrItemDecoder, $arrItemGen] = DecoderGenerator::generate();

        $arrGen = Gen::oneOf(
            Gen::nonEmptyArr(Gen::int(), $arrItemGen),
            Gen::nonEmptyArrList($arrItemGen),
        );

        forAll($arrGen)
            ->withMaxSize(50)
            ->then(
                Check::thatValidFor(nonEmptyArr(int(), $arrItemDecoder))
            );
    }

    public function testValidForAllArraysWithStringKey(): void
    {
        [$arrItemDecoder, $arrItemGen] = DecoderGenerator::generate();

        forAll(Gen::nonEmptyArr(Gen::arrKey('string'), $arrItemGen))
            ->withMaxSize(50)
            ->then(
                Check::thatValidFor(nonEmptyArr(string(), $arrItemDecoder))
            );
    }

    public function testInvalidForAllNotNonEmptyArrays(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $v) => !Predicate::isNonEmptyArray($v))
            ->then(
                Check::thatInvalidFor(nonEmptyArr(arrKey(), mixed()))
            );
    }
}
