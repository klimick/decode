<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Eris\Generator\AssociativeArrayGenerator;
use Klimick\Decode\Test\Helper\Check;
use Klimick\Decode\Test\Helper\DecoderGenerator;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\tuple;
use function Klimick\Decode\Test\Helper\forAll;

final class TupleDecoderTest extends TestCase
{
    public function testValid(): void
    {
        [$firstPropDecoder, $firstPropGenerator] = DecoderGenerator::generate();
        [$secondPropDecoder, $secondPropGenerator] = DecoderGenerator::generate();

        $tupleDecoder = tuple($firstPropDecoder, $secondPropDecoder);

        $tupleGenerator = new AssociativeArrayGenerator([
            $firstPropGenerator,
            $secondPropGenerator,
        ]);

        forAll($tupleGenerator)
            ->withMaxSize(50)
            ->then(Check::thatValidFor($tupleDecoder));
    }

    public function testInvalidArity(): void
    {
        $decoder = tuple(string(), int());
        $tuple = ['str_val'];

        Check::thatInvalidFor($decoder)($tuple);
    }

    public function testTypeError(): void
    {
        $decoder = tuple(string(), int());
        $tuple = ['str_val', 'non_int_value'];

        Check::thatInvalidFor($decoder)($tuple);
    }
}
