<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

final class WithNameDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('Person', shape(id: int(), name: string())->rename('Person'));
    }

    public function testDecodeFailedWhenOriginalDecoderFail(): void
    {
        $decoder = int()->rename('Integer');

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), '42')
            ]),
            actual: decode('42', $decoder),
        );
    }

    public function testDecodeSuccessWhenOriginalDecoderSuccess(): void
    {
        $decoder = int()->rename('Integer');

        Assert::decodeSuccess(
            expectedValue: 42,
            actualDecoded: decode(42, $decoder),
        );
    }
}
