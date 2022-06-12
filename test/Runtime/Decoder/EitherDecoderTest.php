<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Either\Either;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\either;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\string;

final class EitherDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('Either<int, string>', either(int(), string()));
    }

    public function testDecodeFailed(): void
    {
        $decoder = either(int(), string());
        $value = null;

        Assert::decodeFailed(
            expectedReport: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actualDecoded: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = either(int(), string());

        Assert::decodeSuccess(
            expectedValue: Either::left(1),
            actualDecoded: decode(1, $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: Either::right('1'),
            actualDecoded: decode('1', $decoder),
        );
    }
}
