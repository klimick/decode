<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\null;

final class NullDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('null', null());
    }

    public function testDecodeFailed(): void
    {
        $decoder = null();
        $value = '1';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = null();

        Assert::decodeSuccess(
            expected: null,
            actual: decode(null, $decoder),
        );
    }
}
