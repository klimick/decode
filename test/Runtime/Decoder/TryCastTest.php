<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Error\CastException;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\tryCast;
use function PHPUnit\Framework\assertEquals;

final class TryCastTest extends TestCase
{
    public function testTryCastThrowsExceptionWhenFailed(): void
    {
        $decoder = int();

        $this->expectExceptionObject(
            new CastException(
                new ErrorReport([
                    new TypeErrorReport('$', $decoder->name(), 'str value')
                ]),
                $decoder->name(),
            ),
        );

        $_ = tryCast('str value', $decoder);
    }

    public function testTryCastReturnValueWhenSuccess(): void
    {
        assertEquals(42, tryCast(42, int()));
    }
}
