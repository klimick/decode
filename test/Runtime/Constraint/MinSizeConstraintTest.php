<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\minSize;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\listOf;

final class MinSizeConstraintTest extends TestCase
{
    public function testDecodeFailedWhenArraySizeIsLessThanRequired(): void
    {
        $constraint = minSize(is: 3);
        $decoder = listOf(int())->constrained($constraint);

        $value = [1, 2];

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $value, $constraint->metadata()),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenArraySizeIsEqualThanRequired(): void
    {
        $constraint = minSize(is: 3);
        $decoder = listOf(int())->constrained($constraint);

        $value = [1, 2, 3];

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenArraySizeIsMoreThanRequired(): void
    {
        $constraint = minSize(is: 3);
        $decoder = listOf(int())->constrained($constraint);

        $value = [1, 2, 3, 4];

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }
}
