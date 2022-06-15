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
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = bool();

        Assert::decodeSuccess(
            expected: true,
            actual: decode(true, $decoder),
        );

        Assert::decodeSuccess(
            expected: false,
            actual: decode(false, $decoder),
        );
    }
}
