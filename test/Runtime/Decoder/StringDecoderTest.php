<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\string;

final class StringDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('string', string());
    }

    public function testDecodeFailed(): void
    {
        $decoder = string();
        $value = 1;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = string();
        $value = 'some string value';

        Assert::decodeSuccess(
            expectedValue: $value,
            actualDecoded: decode($value, $decoder),
        );
    }
}
