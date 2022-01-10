<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Helper\Predicate;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\listOf;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Test\Helper\forAll;

final class ArrListDecoderTest extends TestCase
{
    public function testValidForAllLists(): void
    {
        [$listItemDecoder, $listItemGen] = DecoderGenerator::generate();

        $listGen = Gen::oneOf(
            Gen::arrList($listItemGen),
            Gen::nonEmptyArrList($listItemGen),
        );

        forAll($listGen)
            ->withMaxSize(50)
            ->then(
                Check::thatValidFor(listOf($listItemDecoder))
            );
    }

    public function testInvalidForAllNotLists(): void
    {
        forAll(Gen::mixed())
            ->when(fn(mixed $v) => !Predicate::isList($v))
            ->then(
                Check::thatInvalidFor(listOf(mixed()))
            );
    }
}
