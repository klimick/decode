<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Option\Option;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\int;
use function PHPUnit\Framework\assertEquals;

final class CastTest extends TestCase
{
    public function testTryCastThrowsExceptionWhenFailed(): void
    {
        assertEquals(Option::none(), cast('str value', int()));
    }

    public function testTryCastReturnValueWhenSuccess(): void
    {
        assertEquals(Option::some(42), cast(42, int()));
    }
}
