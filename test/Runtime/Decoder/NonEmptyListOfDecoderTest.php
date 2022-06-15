<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\nonEmptyListOf;

final class NonEmptyListOfDecoderTest extends TestCase
{
    public function testFailedWhenArrayIsEmpty(): void
    {
        $decoder = nonEmptyListOf(int());

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), [])
            ]),
            actual: decode([], $decoder),
        );
    }

    public function testSuccessWhenArrayIsNotEmpty(): void
    {
        $decoder = nonEmptyListOf(int());

        Assert::decodeSuccess(
            expected: [42],
            actual: decode([42], $decoder),
        );
    }
}
