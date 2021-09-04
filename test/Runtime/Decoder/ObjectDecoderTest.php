<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Option\None;
use Fp\Functional\Option\Some;
use Klimick\Decode\Test\Static\Fixtures\Messenger\Owner\Bot;
use Klimick\Decode\Test\Static\Fixtures\Person;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\string;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

final class ObjectDecoderTest extends TestCase
{
    public function testValid(): void
    {
        $decoder = object(Person::class)(
            name: string(),
            age: int(),
        );

        $decoded = cast(['name' => 'foo', 'age' => 42], $decoder);

        assertInstanceOf(Some::class, $decoded);
    }

    public function testValidWithAssertion(): void
    {
        $decoder = object(Person::class)(
            name: string(),
            age: int(),
        );

        /** @var mixed $value */
        $value = new Person('foo', 42);

        assertTrue($decoder->is($value));
    }

    public function testInvalidWithAssertion(): void
    {
        $decoder = object(Person::class)(
            name: string(),
            age: int(),
        );

        /** @var mixed $value */
        $value = new Bot('foo', 'v1');

        assertFalse($decoder->is($value));
    }

    public function testInvalid(): void
    {
        $decoder = object(Person::class)(
            name: string(),
            age: int(),
        );

        $decoded = cast(['name' => 'foo', 'age' => '42'], $decoder);

        assertInstanceOf(None::class, $decoded);
    }
}
