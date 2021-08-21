<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Test\Static\Decoder\Fixtures\PartialPerson;
use Klimick\Decode\Test\Static\Decoder\Fixtures\Person;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\arr;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Test\Helper\anyValue;

final class PartialObjectDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), object(PartialPerson::class)(
            name: string(),
            age: int(),
        ));

        self::assertTypePerson($decoded);
    }

    public function testMisspellPropertyName(): void
    {
        // /** @psalm-suppress DecodeIssue */
        // $_decoded = cast(anyValue(), object(PartialPerson::class)(
        //     misspelled_name: string(),
        //     age: int(),
        // ));
    }

    /**
     * @param Option<PartialPerson> $_param
     */
    private static function assertTypePerson(Option $_param): void
    {
    }
}
