<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;

final class IntDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('int', int());
    }

    public function testDecodeFailed(): void
    {
        $decoder = int();
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
        $decoder = int();
        $value = 1;

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }
}
