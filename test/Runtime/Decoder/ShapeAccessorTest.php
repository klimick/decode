<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\ContextEntry;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Decoder\Invalid;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Decoder\Valid;
use Klimick\Decode\Internal\Shape\ShapeAccessor;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Decoder\constant;
use function PHPUnit\Framework\assertEquals;

/**
 * @psalm-type CaseName = string
 * @psalm-type CaseData = array{
 *     decoder: AbstractDecoder,
 *     field: string,
 *     shape: array,
 *     expected: Either,
 * }
 */
final class ShapeAccessorTest extends TestCase
{
    /**
     * @dataProvider provideCases
     */
    public function testShapeAccessor(AbstractDecoder $decoder, string $key, array $shape, Either $expected): void
    {
        $context = new Context([
            new ContextEntry($decoder->name(), $shape, $key),
        ]);

        $actual = ShapeAccessor::decodeProperty($context, $decoder, $key, $shape);
        assertEquals($expected, $actual);
    }

    /**
     * @return iterable<CaseName, CaseData>
     */
    public function provideCases(): iterable
    {
        $anyDecoder = mixed();
        $anyField = 'any_field_name';

        $valid = fn(array $val): Either => Either::right(new Valid($val));

        $undefinedProperty = Either::left(
            new Invalid([
                new UndefinedError(
                    new Context([
                        new ContextEntry($anyDecoder->name(), [], $anyField),
                    ])
                )
            ])
        );

        yield 'field does not exist' => [
            'decoder' => $anyDecoder,
            'field' => $anyField,
            'shape' => [],
            'expected' => $undefinedProperty,
        ];

        yield 'field exists' => [
            'decoder' => $anyDecoder,
            'field' => $anyField,
            'shape' => [$anyField => 'val'],
            'expected' => $valid([$anyField => 'val']),
        ];

        yield 'aliased field does not exist' => [
            'decoder' => $anyDecoder->from('$.some_field.path'),
            'field' => $anyField,
            'shape' => [],
            'expected' => $undefinedProperty,
        ];

        yield 'aliased field exists' => [
            'decoder' => $anyDecoder->from('$.some_field.path'),
            'field' => $anyField,
            'shape' => ['some_field' => ['path' => 'val']],
            'expected' => $valid([$anyField => 'val']),
        ];

        yield 'from self' => [
            'decoder' => $anyDecoder->from('$'),
            'field' => $anyField,
            'shape' => ['val1' => 10, 'val2' => 20],
            'expected' => $valid([$anyField => ['val1' => 10, 'val2' => 20]]),
        ];

        yield 'with default' => [
            'decoder' => $anyDecoder->default(10),
            'field' => $anyField,
            'shape' => [],
            'expected' => $valid([$anyField => 10]),
        ];

        yield 'optional field' => [
            'decoder' => $anyDecoder->optional(),
            'field' => $anyField,
            'shape' => [],
            'expected' => $valid([]),
        ];

        yield 'constant field' => [
            'decoder' => constant(10),
            'field' => $anyField,
            'shape' => [],
            'expected' => $valid([$anyField => 10]),
        ];
    }
}
