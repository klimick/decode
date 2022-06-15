<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\arrayKey;
use function Klimick\Decode\Decoder\decode;

final class ArrayKeyDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('array-key', arrayKey());
    }

    public function testDecodeFailedWithNonArrayKeyValue(): void
    {
        $decoder = arrayKey();

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), 1.42),
            ]),
            actual: decode(1.42, $decoder),
        );
    }

    public function testDecodeSuccessForIntArrayKey(): void
    {
        Assert::decodeSuccess(
            expected: 42,
            actual: decode(42, arrayKey()),
        );
    }

    public function testDecodeSuccessForStringArrayKey(): void
    {
        Assert::decodeSuccess(
            expected: 'key-42',
            actual: decode('key-42', arrayKey()),
        );
    }
}
