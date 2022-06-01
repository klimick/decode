<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Option\None;
use Fp\Functional\Option\Some;
use Klimick\Decode\Test\Static\Fixtures\Person;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\string;
use function PHPUnit\Framework\assertInstanceOf;

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
