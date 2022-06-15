<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\equal;
use function Klimick\Decode\Constraint\not;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\nonEmptyString;

final class NotConstraintTest extends TestCase
{
    public function testDecodeFailedIfConstraintSuccess(): void
    {
        $constraint = not(equal(to: 'test'));
        $decoder = nonEmptyString()->constrained($constraint);

        $invalidValue = 'test';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValue, $constraint->metadata()),
            ]),
            actual: decode($invalidValue, $decoder),
        );
    }

    public function testDecodeSuccessIfConstraintFailed(): void
    {
        $constraint = not(equal(to: 'test'));
        $decoder = nonEmptyString()->constrained($constraint);

        $validValue = 'not-test';

        Assert::decodeSuccess(
            expected: $validValue,
            actual: decode($validValue, $decoder),
        );
    }
}
