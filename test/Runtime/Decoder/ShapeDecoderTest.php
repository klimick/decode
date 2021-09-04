<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Eris\Generator\AssociativeArrayGenerator;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\constant;
use function Klimick\Decode\Test\Helper\forAll;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertTrue;

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

    public function testAliased(): void
    {
        $decoder = shape(
            name: string()->from('$.person_name'),
            age: int()->from('$.person_age'),
            is_person: bool()->default(true),
            is_admin: bool()->optional(),
            is_approved: constant(true),
        );

        $data = [
            'person_name' => 'foo',
            'person_age' => 42,
        ];

        $decoded = cast($data, $decoder)->get();

        assertNotNull($decoded);
        assertEquals(['name' => 'foo', 'age' => 42, 'is_person' => true, 'is_approved' => true], $decoded);
    }

    public function testValidWithTypeAssertion(): void
    {
        $decoder = shape(name: string());

        /** @var mixed $value */
        $value = ['name' => ''];

        assertTrue($decoder->is($value));
    }

    public function testInvalidWithTypeAssertion(): void
    {
        $decoder = shape(name: string());

        /** @var mixed $value */
        $value = null;

        assertFalse($decoder->is($value));
    }
}
