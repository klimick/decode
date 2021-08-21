<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Test\Static\Decoder\Fixtures\Person;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Test\Helper\anyValue;

final class ObjectDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), object(Person::class)(
            name: string(),
            age: int(),
        ));

        self::assertTypePerson($decoded);
    }

    public function testMisspellPropertyName(): void
    {
         /** @psalm-suppress RequiredObjectPropertyMissingIssue, NonexistentPropertyObjectPropertyIssue */
         $_decoded = cast(anyValue(), object(Person::class)(
             misspelled_name: string(),
             age: int(),
         ));
    }

    public function testInvalidDecoderForProperty(): void
    {
        /** @psalm-suppress InvalidDecoderForPropertyIssue */
        $_decoded = cast(anyValue(), object(Person::class)(
            name: string(),
            age: string(),
        ));
    }

    /**
     * @param Option<Person> $_param
     */
    private static function assertTypePerson(Option $_param): void
    {
    }
}