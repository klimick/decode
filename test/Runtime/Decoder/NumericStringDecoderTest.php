<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\numericString;

final class NumericStringDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('numeric-string', numericString());
    }

    public function testDecodeFailed(): void
    {
        $decoder = numericString();
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
        $decoder = numericString();

        Assert::decodeSuccess(
            expected: '1.0',
            actual: decode('1.0', $decoder),
        );
        Assert::decodeSuccess(
            expected: '1',
            actual: decode('1', $decoder),
        );
    }
}
