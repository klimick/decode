<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\andX;
use function Klimick\Decode\Constraint\endsWith;
use function Klimick\Decode\Constraint\startsWith;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\nonEmptyString;

final class AndConstraintTest extends TestCase
{
    public function testDecodeFailedIfSomeConstraintsFailed(): void
    {
        $constraint = andX(startsWith('val: '), endsWith(';'));
        $decoder = nonEmptyString()->constrained($constraint);

        $invalidValue = 'val: test';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValue, $constraint->metadata())
            ]),
            actual: decode($invalidValue, $decoder),
        );
    }

    public function testDecodeSuccessIfAllConstraintsSuccess(): void
    {
        $constraint = andX(startsWith('val: '), endsWith(';'));
        $decoder = nonEmptyString()->constrained($constraint);

        $validValue = 'val: test;';

        Assert::decodeSuccess(
            expected: $validValue,
            actual: decode($validValue, $decoder),
        );
    }
}
