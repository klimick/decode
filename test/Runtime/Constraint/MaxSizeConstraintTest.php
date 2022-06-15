<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\maxSize;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\listOf;

final class MaxSizeConstraintTest extends TestCase
{
    public function testDecodeFailedWhenArraySizeIsMoreThanRequired(): void
    {
        $constraint = maxSize(is: 3);
        $decoder = listOf(int())->constrained($constraint);

        $value = [1, 2, 3, 4];

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $value, $constraint->metadata()),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenArraySizeIsEqualToRequired(): void
    {
        $constraint = maxSize(is: 3);
        $decoder = listOf(int())->constrained($constraint);

        $value = [1, 2, 3];
        
        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenArraySizeIsLessThanRequired(): void
    {
        $constraint = maxSize(is: 3);
        $decoder = listOf(int())->constrained($constraint);

        $value = [1, 2];

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }
}
