<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\trimmed;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\string;

final class TrimmedConstraintTest extends TestCase
{
    public function testDecodeFailedWhenStringHasLeadingSpace(): void
    {
        $constraint = trimmed();
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = ' str-value';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $valueWithLeadingSpace, $constraint->metadata())
            ]),
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }

    public function testDecodeFailedWhenStringHasLeadingSpaces(): void
    {
        $constraint = trimmed();
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = '   str-value';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $valueWithLeadingSpace, $constraint->metadata())
            ]),
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }

    public function testDecodeFailedWhenStringHasTrailingSpace(): void
    {
        $constraint = trimmed();
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = 'str-value ';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $valueWithLeadingSpace, $constraint->metadata())
            ]),
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }

    public function testDecodeFailedWhenStringHasTrailingSpaces(): void
    {
        $constraint = trimmed();
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = 'str-value   ';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $valueWithLeadingSpace, $constraint->metadata())
            ]),
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }

    public function testDecodeFailedWhenStringHasLeadingAndTrailingSpaces(): void
    {
        $constraint = trimmed();
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = '  str-value   ';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $valueWithLeadingSpace, $constraint->metadata())
            ]),
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }

    public function testDecodeSuccessWhenStringHasNoLeadingOrTrailingSpaces(): void
    {
        $constraint = trimmed();
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = 'str-value';

        Assert::decodeSuccess(
            expected: $valueWithLeadingSpace,
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }
}
