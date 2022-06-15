<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\inRange;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;

final class InRangeConstraintTest extends TestCase
{
    public function testDecodeFailedWhenNumberNotInRequiredRange(): void
    {
        $constraint = inRange(from: 10, to: 20);
        $decoder = int()->constrained($constraint);

        $value = 30;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $value, $constraint->metadata())
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenNumberIsEqToBottom(): void
    {
        $constraint = inRange(from: 10, to: 20);
        $decoder = int()->constrained($constraint);

        $value = 10;

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenNumberIsEqToTop(): void
    {
        $constraint = inRange(from: 10, to: 20);
        $decoder = int()->constrained($constraint);

        $value = 20;

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenNumberInRange(): void
    {
        $constraint = inRange(from: 10, to: 20);
        $decoder = int()->constrained($constraint);

        $value = 15;

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }
}
