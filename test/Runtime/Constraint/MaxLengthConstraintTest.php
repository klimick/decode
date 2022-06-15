<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\maxLength;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\string;

final class MaxLengthConstraintTest extends TestCase
{
    public function testDecodeFailedWhenStringLengthMoreThanRequired(): void
    {
        $constraint = maxLength(is: 10);
        $decoder = string()->constrained($constraint);

        $largeString = 'str-test-large';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $largeString, $constraint->metadata()),
            ]),
            actual: decode($largeString, $decoder),
        );
    }

    public function testDecodeSuccessWhenStringLengthLessThanRequired(): void
    {
        $constraint = maxLength(is: 10);
        $decoder = string()->constrained($constraint);

        $largeString = 'str-test';

        Assert::decodeSuccess(
            expected: $largeString,
            actual: decode($largeString, $decoder),
        );
    }
}
