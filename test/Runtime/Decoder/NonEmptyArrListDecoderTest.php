<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use Klimick\Decode\Test\Helper\Gen;
use Klimick\Decode\Test\Helper\Predicate;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Decoder\nonEmptyListOf;
use function Klimick\Decode\Test\Helper\forAll;

final class NonEmptyArrListDecoderTest extends TestCase
{
    public function testValidForAllNonEmptyLists(): void
    {
        [$itemDecoder, $itemGen] = DecoderGenerator::generate();

        forAll(Gen::nonEmptyArrList($itemGen))
            ->withMaxSize(50)
            ->then(
                Check::thatValidFor(nonEmptyListOf($itemDecoder))
            );
    }

    public function testInvalidForAllNotNonEmptyLists(): void
    {
        forAll(Gen::mixed())
            ->withMaxSize(50)
            ->when(fn(mixed $v) => !Predicate::isNonEmptyList($v))
            ->then(
                Check::thatInvalidFor(nonEmptyListOf(mixed()))
            );
    }
}
