<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\minLength;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\float;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\union;

final class UnionDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('int | string | bool', union(int(), string(), bool()));
    }

    public function testDecodeFailed(): void
    {
        $decoder = union(int(), string(), bool());
        $value = null;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testMergeErrorsForTheSameField(): void
    {
        $constraint = minLength(is: 2);
        $decoder = union(
            shape(
                test1: string(),
                test2: int()->from('$.test1alias'),
                test3: string()->constrained($constraint),
            ),
            shape(
                test1: union(int(), float()),
                test2: int()->from('$.test2alias'),
                test3: string()->constrained($constraint),
            ),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport(
                    path: '$.test1',
                    expected: 'string | int | float',
                    actual: ['invalid'],
                ),
                new UndefinedErrorReport(
                    path: '$.test2',
                    aliases: ['$.test1alias', '$.test2alias'],
                ),
                new ConstraintErrorReport(
                    path: '$.test3',
                    value: 'F',
                    meta: $constraint->metadata(),
                ),
            ]),
            actual: decode(['test1' => ['invalid'], 'test3' => 'F'], $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = union(int(), string(), bool());

        Assert::decodeSuccess(
            expectedValue: 1,
            actualDecoded: decode(1, $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: 'str',
            actualDecoded: decode('str', $decoder),
        );
        Assert::decodeSuccess(
            expectedValue: true,
            actualDecoded: decode(true, $decoder),
        );
    }
}
