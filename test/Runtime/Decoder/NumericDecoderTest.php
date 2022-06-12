<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\numeric;

final class NumericDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('numeric', numeric());
    }

    public function testDecodeFailed(): void
    {
        $decoder = numeric();
        $value = 'str';

        Assert::decodeFailed(
            expectedReport: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actualDecoded: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = numeric();

        Assert::decodeSuccess(
            expectedValue: 1.0,
            actualDecoded: decode(1.0, $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: 1,
            actualDecoded: decode(1, $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: '1',
            actualDecoded: decode('1', $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: '1.0',
            actualDecoded: decode('1.0', $decoder),
        );
    }
}
