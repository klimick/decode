<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Test\Helper\forAll;

final class MixedDecoderTest extends TestCase
{
    public function testValidForAllValues(): void
    {
        forAll(Gen::mixed())->then(Check::thatValidFor(mixed()));
    }
}
