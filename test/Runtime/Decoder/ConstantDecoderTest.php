<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\constantly;
use function Klimick\Decode\Test\Helper\forAll;

final class ConstantDecoderTest extends TestCase
{
    public function testValidForAllGivenConstants(): void
    {
        forAll(Gen::mixed())
            ->then(function(mixed $value) {
                Check::thatValidFor(constantly($value))($value);
            });
    }
}
