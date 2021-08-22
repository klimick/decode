<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\intersection;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Test\Helper\anyValue;

/**
 * @psalm-type FirstShape = array{prop1: string, prop2: string}
 * @psalm-type SecondShape = array{prop3: string, prop4: string}
 * @psalm-type ThirdShape = array{prop5: string, prop6: string}
 */
final class IntersectionDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), intersection(
            shape(prop1: string(), prop2: string()),
            shape(prop3: string(), prop4: string()),
            shape(prop5: string(), prop6: string()),
        ));

        self::assertTypePerson($decoded);
    }

    public function testWithCollisions(): void
    {
        /** @psalm-suppress IntersectionCollisionIssue */
        $_decoded = cast(anyValue(), intersection(
            shape(prop1: string(), prop2: string()),
            shape(prop3: string(), prop2: string()),
        ));
    }

    /**
     * @param Option<FirstShape & SecondShape & ThirdShape> $_param
     */
    private static function assertTypePerson(Option $_param): void
    {
    }
}
