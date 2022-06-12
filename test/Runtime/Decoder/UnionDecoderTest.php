<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\union;

final class UnionDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('int | string | bool', union(int(), string(), bool()));
    }

    public function testDecodeFailed(): void
    {
        $decoder = union(int(), string(), bool());
        $value = null;

        Assert::decodeFailed(
            expectedReport: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actualDecoded: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = union(int(), string(), bool());

        Assert::decodeSuccess(
            expectedValue: 1,
            actualDecoded: decode(1, $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: 'str',
            actualDecoded: decode('str', $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: true,
            actualDecoded: decode(true, $decoder),
        );
    }
}
