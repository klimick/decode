<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Eris\Generator\AssociativeArrayGenerator;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\intersection;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Test\Helper\forAll;

final class IntersectionDecoderTest extends TestCase
{
    public function testValid(): void
    {
        [$firstPropDecoder, $firstPropGenerator] = DecoderGenerator::generate();
        [$secondPropDecoder, $secondPropGenerator] = DecoderGenerator::generate();
        [$thirdPropDecoder, $thirdPropGenerator] = DecoderGenerator::generate();

        $intersectionDecoder = intersection(
            shape(firstProp: $firstPropDecoder),
            shape(secondProp: $secondPropDecoder),
            shape(thirdProp: $thirdPropDecoder),
        );

        $shapeGenerator = new AssociativeArrayGenerator([
            'firstProp' => $firstPropGenerator,
            'secondProp' => $secondPropGenerator,
            'thirdProp' => $thirdPropGenerator,
        ]);

        forAll($shapeGenerator)
            ->withMaxSize(50)
            ->then(
                Check::thatValidFor($intersectionDecoder)
            );
    }
}
