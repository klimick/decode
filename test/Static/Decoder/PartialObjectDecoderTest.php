<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Test\Static\Fixtures\PartialPerson;
use Klimick\Decode\Test\Static\Fixtures\Person;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\partialObject;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Test\Helper\anyValue;

final class PartialObjectDecoderTest
{
    public function test(): void
    {
        $decoded = cast(anyValue(), partialObject(PartialPerson::class)(
            name: string(),
            age: int(),
        ));

        self::assertTypePartialPerson($decoded);
    }

    public function testMisspellPropertyName(): void
    {
        /** @psalm-suppress RequiredObjectPropertyMissingIssue, NonexistentPropertyObjectPropertyIssue */
        $_decoded = cast(anyValue(), partialObject(PartialPerson::class)(
            misspelled_name: string(),
            age: int(),
        ));
    }

    public function testInvalidDecoderForProperty(): void
    {
        /** @psalm-suppress InvalidDecoderForPropertyIssue */
        $_decoded = cast(anyValue(), partialObject(PartialPerson::class)(
            name: string(),
            age: string(),
        ));
    }

    public function testInvalidPartialObject(): void
    {
        /** @psalm-suppress NotPartialPropertyIssue */
        $_decoded = cast(anyValue(), partialObject(Person::class)(
            name: string(),
            age: int(),
        ));
    }

    /**
     * @param Option<PartialPerson> $_param
     */
    private static function assertTypePartialPerson(Option $_param): void
    {
    }
}
