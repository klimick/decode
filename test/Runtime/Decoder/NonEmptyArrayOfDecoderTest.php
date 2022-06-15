<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\nonEmptyArrayOf;
use function Klimick\Decode\Decoder\string;

final class NonEmptyArrayOfDecoderTest extends TestCase
{
    public function testFailedWhenArrayIsEmpty(): void
    {
        $decoder = nonEmptyArrayOf(int(), string());

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), [])
            ]),
            actual: decode([], $decoder),
        );
    }

    public function testSuccessWhenArrayIsNotEmpty(): void
    {
        $decoder = nonEmptyArrayOf(int(), string());

        Assert::decodeSuccess(
            expectedValue: [42 => 'success'],
            actualDecoded: decode([42 => 'success'], $decoder),
        );
    }
}
