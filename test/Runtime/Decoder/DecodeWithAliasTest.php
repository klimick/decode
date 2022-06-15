<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;

final class DecodeWithAliasTest extends TestCase
{
    public function testDecodeFromAliasFailed(): void
    {
        $decoder = int()->from('$.number');

        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$', ['$.number']),
            ]),
            actual: decode(['__number' => 42], $decoder),
        );
    }

    public function testDecoderFromAliasSuccess(): void
    {
        $decoder = int()->from('$.number');

        Assert::decodeSuccess(
            expectedValue: 42,
            actualDecoded: decode(['number' => 42], $decoder),
        );
    }
}
