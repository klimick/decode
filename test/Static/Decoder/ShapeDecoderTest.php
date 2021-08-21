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
 * @psalm-type WithPartialExpectedShapeType = array{
 *     name: string,
 *     age?: int,
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

        self::assertTypeShape(cast(anyValue(), $shape));
    }

    public function testWithOptionalProperty(): void
    {
        $shape = shape(
            name: string(),
            age: int()->optional(),
            address: shape(
                street: string(),
                postcode: int(),
            ),
        );

        self::assertTypeShapeWithOptional(cast(anyValue(), $shape));
    }

    public function testWithOptionalPropertyWithDefaultValue(): void
    {
        /** @psalm-suppress OptionalCallContradictionIssue */
        $_shape = shape(
            name: string(),
            age: int()->default(42)->optional(),
            address: shape(
                street: string(),
                postcode: int(),
            ),
        );
    }

    public function testWithAliasedProperty(): void
    {
        /** @psalm-suppress InvalidPropertyAliasIssue */
        $_shape = shape(
            name: string(),
            age: int()->from('person_age'),
            address: shape(
                street: string(),
                postcode: int(),
            ),
        );
    }

    /**
     * @param Option<ExpectedShapeType> $_param
     */
    private static function assertTypeShape(Option $_param): void
    {
    }

    /**
     * @param Option<WithPartialExpectedShapeType> $_param
     */
    private static function assertTypeShapeWithOptional(Option $_param): void
    {
    }
}
