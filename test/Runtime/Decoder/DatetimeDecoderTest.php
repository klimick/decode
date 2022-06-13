<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use DateTimeImmutable;
use DateTimeZone;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\datetime;
use function Klimick\Decode\Decoder\decode;

final class DatetimeDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name(DateTimeImmutable::class, datetime());
    }

    public function testDecodeFailure(): void
    {
        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', DateTimeImmutable::class, 'Wrong datetime value')
            ]),
            actual: decode('Wrong datetime value', datetime()),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', DateTimeImmutable::class, '23:00:00 2022-06-11')
            ]),
            actual: decode('23:00:00 2022-06-11', datetime(fromFormat: 'Y-m-d H:i:s')),
        );
    }

    public function testDecodeSuccess(): void
    {
        Assert::decodeSuccess(
            expectedValue: new DateTimeImmutable('2022-06-11 23:00:00', new DateTimeZone('UTC')),
            actualDecoded: decode('2022-06-11 23:00:00', datetime()),
        );

        Assert::decodeSuccess(
            expectedValue: new DateTimeImmutable('2022-06-11 23:00:00', new DateTimeZone('UTC')),
            actualDecoded: decode('2022-06-11 23:00:00', datetime(fromFormat: 'Y-m-d H:i:s')),
        );
    }
}
