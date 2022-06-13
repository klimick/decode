<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\scalar;

final class ScalarDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('scalar', scalar());
    }

    public function testDecodeFailed(): void
    {
        $decoder = scalar();
        $value = new stdClass();

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = scalar();

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
            expectedValue: true,
            actualDecoded: decode(true, $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: false,
            actualDecoded: decode(false, $decoder),
        );
    }
}
