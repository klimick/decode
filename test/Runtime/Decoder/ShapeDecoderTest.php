<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Eris\Generator\AssociativeArrayGenerator;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Test\Helper\forAll;

final class ShapeDecoderTest extends TestCase
{
    public function testValid(): void
    {
        [$firstPropDecoder, $firstPropGenerator] = DecoderGenerator::generate();
        [$secondPropDecoder, $secondPropGenerator] = DecoderGenerator::generate();
        [$thirdPropDecoder, $thirdPropGenerator] = DecoderGenerator::generate();

        $shapeDecoder = shape(
            firstProp: $firstPropDecoder,
            secondProp: $secondPropDecoder,
            thirdProp: $thirdPropDecoder,
        );

        $shapeGenerator = new AssociativeArrayGenerator([
            'firstProp' => $firstPropGenerator,
            'secondProp' => $secondPropGenerator,
            'thirdProp' => $thirdPropGenerator,
        ]);

        forAll($shapeGenerator)
            ->withMaxSize(50)
            ->then(Check::thatValidFor($shapeDecoder));
    }
}
