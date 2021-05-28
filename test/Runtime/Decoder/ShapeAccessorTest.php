<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Internal\Shape\ShapeAccessor;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\aliased;
use function Klimick\Decode\Decoder\fromSelf;
use function Klimick\Decode\Decoder\mixed;
use function PHPUnit\Framework\assertEquals;

/**
 * @psalm-type CaseName = string
 * @psalm-type CaseData = array{
 *     decoder: AbstractDecoder,
 *     field: string,
 *     shape: array,
 *     expected: Option,
 * }
 */
final class ShapeAccessorTest extends TestCase
{
    /**
     * @dataProvider provideCases
     */
    public function testShapeAccessor(AbstractDecoder $decoder, int|string $key, array $shape, Option $expected): void
    {
        $actual = ShapeAccessor::access($decoder, $key, $shape);
        assertEquals($expected, $actual);
    }

    /**
     * @return iterable<CaseName, CaseData>
     */
    public function provideCases(): iterable
    {
        $anyDecoder = mixed();
        $anyField = 'does not matter';

        yield 'field does not exist' => [
            'decoder' => $anyDecoder,
            'field' => 'some_field',
            'shape' => [],
            'expected' => Option::none(),
        ];

        yield 'field exists' => [
            'decoder' => $anyDecoder,
            'field' => 'some_field',
            'shape' => ['some_field' => 'val'],
            'expected' => Option::some('val'),
        ];

        yield 'aliased field does not exist' => [
            'decoder' => aliased($anyDecoder, 'some_field.path'),
            'field' => $anyField,
            'shape' => [],
            'expected' => Option::none(),
        ];

        yield 'aliased field exists' => [
            'decoder' => aliased($anyDecoder, 'some_field.path'),
            'field' => $anyField,
            'shape' => ['some_field' => ['path' => 'val']],
            'expected' => Option::some('val'),
        ];

        yield 'from self' => [
            'decoder' => fromSelf($anyDecoder),
            'field' => $anyField,
            'shape' => ['val' => 10],
            'expected' => Option::some(['val' => 10]),
        ];
    }
}
