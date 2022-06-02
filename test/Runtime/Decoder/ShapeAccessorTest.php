<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Context;
use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Decoder\UndefinedError;
use Klimick\Decode\Internal\Shape\ShapeAccessor;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\mixed;
use function Klimick\Decode\Decoder\constant;
use function PHPUnit\Framework\assertEquals;

/**
 * @psalm-type CaseName = string
 * @psalm-type CaseData = array{
 *     decoder: DecoderInterface,
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
    public function testShapeAccessor(DecoderInterface $decoder, string $key, array $shape, Either $expected): void
    {
        $context = Context::root($decoder->name(), $shape);

        $actual = ShapeAccessor::decodeProperty($context, $decoder, $key, $shape);
        assertEquals($expected, $actual);
    }

    /**
     * @return iterable<CaseName, CaseData>
     */
    public function provideCases(): iterable
    {
        $decoder = mixed();
        $field = 'any_field_name';

        $valid = fn(mixed $val): Either => Either::right($val);

        $undefinedProperty = Either::left([
            new UndefinedError(
                Context::root(name: $decoder->name(), actual: [])(
                    name: $decoder->name(),
                    actual: null,
                    key: $field,
                ),
            )
        ]);

        yield 'field does not exist' => [
            'decoder' => $decoder,
            'field' => $field,
            'shape' => [],
            'expected' => $undefinedProperty,
        ];

        yield 'field exists' => [
            'decoder' => $decoder,
            'field' => $field,
            'shape' => [$field => 'val'],
            'expected' => $valid('val'),
        ];

        yield 'aliased field does not exist' => [
            'decoder' => $decoder->from('$.some_field.path'),
            'field' => $field,
            'shape' => [],
            'expected' => $undefinedProperty,
        ];

        yield 'aliased field exists' => [
            'decoder' => $decoder->from('$.some_field.path'),
            'field' => $field,
            'shape' => ['some_field' => ['path' => 'val']],
            'expected' => $valid('val'),
        ];

        yield 'from self' => [
            'decoder' => $decoder->from('$'),
            'field' => $field,
            'shape' => ['val1' => 10, 'val2' => 20],
            'expected' => $valid(['val1' => 10, 'val2' => 20]),
        ];

        yield 'with default' => [
            'decoder' => $decoder->default(10),
            'field' => $field,
            'shape' => [],
            'expected' => $valid(10),
        ];

        yield 'constant field' => [
            'decoder' => constant(10),
            'field' => $field,
            'shape' => [],
            'expected' => $valid(10),
        ];
    }
}
