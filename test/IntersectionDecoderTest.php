<?php

declare(strict_types=1);

namespace Klimick\Decode\Test;

use Eris\Generator\AssociativeArrayGenerator;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\intersection;
use function Klimick\Decode\shape;
use function Klimick\Decode\Test\Helper\forAll;

final class IntersectionDecoderTest extends TestCase
{
    public function testValid(): void
    {
        [$firstPropDecoder, $firstPropGenerator] = DecoderGenerator::generate();
        [$secondPropDecoder, $secondPropGenerator] = DecoderGenerator::generate();
        [$thirdPropDecoder, $thirdPropGenerator] = DecoderGenerator::generate();

        $firstShapeDecoder = shape(
            firstProp: $firstPropDecoder,
        );
        $secondShapeDecoder = shape(
            secondProp: $secondPropDecoder,
        );
        $thirdShapeDecoder = shape(
            thirdProp: $thirdPropDecoder,
        );

        $intersectionDecoder = intersection(
            $firstShapeDecoder,
            $secondShapeDecoder,
            $thirdShapeDecoder,
        );

        $shapeGenerator = new AssociativeArrayGenerator([
            'firstProp' => $firstPropGenerator,
            'secondProp' => $secondPropGenerator,
            'thirdProp' => $thirdPropGenerator,
        ]);

        forAll($shapeGenerator)
            ->withMaxSize(50)
            ->then(Check::thatValidFor($intersectionDecoder));
    }
}
