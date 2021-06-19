<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\scalar;
use function Klimick\Decode\Test\Helper\forAll;

final class ScalarDecoderTest extends TestCase
{
    public function testValidForAllScalars(): void
    {
        forAll(Gen::scalar())->then(Check::thatValidFor(scalar()));
    }

    public function testInvalidForAllNotScalars(): void
    {
        forAll(Gen::scalarSeq())->then(Check::thatInvalidFor(scalar()));
    }
}
