<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\greater;
use function Klimick\Decode\Constraint\greaterOrEqual;
use function Klimick\Decode\Constraint\less;
use function Klimick\Decode\Constraint\lessOrEqual;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;

final class ComparisonConstraintTest extends TestCase
{
    public function testLessFailed(): void
    {
        $constraint = less(than: 42);
        $decoder = int()->constrained($constraint);

        $invalidValue = 42;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValue, $constraint->metadata()),
            ]),
            actual: decode($invalidValue, $decoder),
        );
    }

    public function testLessSuccess(): void
    {
        $constraint = less(than: 42);
        $decoder = int()->constrained($constraint);

        $validValue = 41;

        Assert::decodeSuccess(
            expected: $validValue,
            actual: decode($validValue, $decoder),
        );
    }

    public function testLessOrEqualFailed(): void
    {
        $constraint = lessOrEqual(to: 42);
        $decoder = int()->constrained($constraint);

        $invalidValue = 43;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValue, $constraint->metadata()),
            ]),
            actual: decode($invalidValue, $decoder),
        );
    }

    public function testLessOrEqualSuccess(): void
    {
        $constraint = lessOrEqual(to: 42);
        $decoder = int()->constrained($constraint);

        Assert::decodeSuccess(expected: 42, actual: decode(42, $decoder));
        Assert::decodeSuccess(expected: 41, actual: decode(41, $decoder));
    }

    public function testGreaterFailed(): void
    {
        $constraint = greater(than: 42);
        $decoder = int()->constrained($constraint);

        $invalidValue = 41;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValue, $constraint->metadata()),
            ]),
            actual: decode($invalidValue, $decoder),
        );
    }

    public function testGreaterSuccess(): void
    {
        $constraint = greater(than: 42);
        $decoder = int()->constrained($constraint);

        $validValue = 43;

        Assert::decodeSuccess(
            expected: $validValue,
            actual: decode($validValue, $decoder),
        );
    }

    public function testGreaterOrEqualFailed(): void
    {
        $constraint = greaterOrEqual(to: 42);
        $decoder = int()->constrained($constraint);

        $invalidValue = 41;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValue, $constraint->metadata()),
            ]),
            actual: decode($invalidValue, $decoder),
        );
    }

    public function testGreaterOrEqualSuccess(): void
    {
        $constraint = greaterOrEqual(to: 42);
        $decoder = int()->constrained($constraint);

        Assert::decodeSuccess(expected: 42, actual: decode(42, $decoder));
        Assert::decodeSuccess(expected: 43, actual: decode(43, $decoder));
    }
}
