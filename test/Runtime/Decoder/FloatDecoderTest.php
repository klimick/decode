<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\float;

final class FloatDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('float', float());
    }

    public function testDecodeFailed(): void
    {
        $decoder = float();
        $value = 1;

        Assert::decodeFailed(
            expectedReport: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actualDecoded: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = float();
        $value = 1.0;

        Assert::decodeSuccess(
            expectedValue: $value,
            actualDecoded: decode($value, $decoder),
        );
    }
}
