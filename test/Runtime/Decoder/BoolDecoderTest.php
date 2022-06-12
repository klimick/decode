<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\bool;

final class BoolDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('bool', bool());
    }

    public function testDecodeFailed(): void
    {
        $decoder = bool();
        $value = '1';

        Assert::decodeFailed(
            expectedReport: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actualDecoded: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = bool();

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
