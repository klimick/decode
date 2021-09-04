<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use Klimick\Decode\Test\Helper\Gen;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Test\Helper\forAll;
use function Klimick\Decode\Decoder\union;

final class UnionDecoderTest extends TestCase
{
    public function testValid(): void
    {
        [$firstDecoder, $firstGenerator] = DecoderGenerator::generate();
        [$secondDecoder, $secondGenerator] = DecoderGenerator::generate();
        [$thirdDecoder, $thirdGenerator] = DecoderGenerator::generate();

        forAll(Gen::oneOf($firstGenerator, $secondGenerator, $thirdGenerator))->then(
            Check::thatValidFor(union($firstDecoder, $secondDecoder, $thirdDecoder))
        );
    }

    public function testInvalid(): void
    {
        $decoder = union(int(), string());
        $data = true;

        Check::thatInvalidFor($decoder)($data);
    }
}
