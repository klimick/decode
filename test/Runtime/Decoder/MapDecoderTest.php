<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;

final class MapDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('MapFrom<int>', int()->map(fn(int $i) => (string) $i));
    }

    public function testDecodeFailed(): void
    {
        $decoder = int()->map(fn(int $i) => (string) $i);
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
        $decoder = int()->map(fn(int $i) => (string) $i);

        Assert::decodeSuccess(
            expected: '1',
            actual: decode(1, $decoder),
        );
    }
}
