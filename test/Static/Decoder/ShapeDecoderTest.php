<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Test\Helper\anyValue;

/**
 * @psalm-type ExpectedShapeType = array{
 *     name: string,
 *     age: int,
 *     address: array{
 *         street: string,
 *         postcode: int,
 *     }
 * }
 */
final class ShapeDecoderTest
{
    public function test(): void
    {
        $shape = shape(
            name: string(),
            age: int(),
            address: shape(
                street: string(),
                postcode: int(),
            ),
        );

        $decoded = cast(anyValue(), $shape);
        self::assertTypeShape($decoded);
    }

    /**
     * @param Option<ExpectedShapeType> $_param
     */
    private static function assertTypeShape(Option $_param): void
    {
    }
}
