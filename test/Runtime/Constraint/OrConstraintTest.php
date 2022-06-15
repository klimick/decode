<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\equal;
use function Klimick\Decode\Constraint\orX;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\nonEmptyString;

final class OrConstraintTest extends TestCase
{
    public function testDecodeFailedIfAllConstraintsFailed(): void
    {
        $constraint = orX(equal(to: 'test-1'), equal(to: 'test-2'));
        $decoder = nonEmptyString()->constrained($constraint);

        $invalidValue = 'test-3';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValue, $constraint->metadata())
            ]),
            actual: decode($invalidValue, $decoder),
        );
    }

    public function testDecodeSuccessIfSomeConstraintsSuccess(): void
    {
        $constraint = orX(equal(to: 'test-1'), equal(to: 'test-2'));
        $decoder = nonEmptyString()->constrained($constraint);

        $validValue = 'test-1';

        Assert::decodeSuccess(
            expected: $validValue,
            actual: decode($validValue, $decoder),
        );
    }
}
