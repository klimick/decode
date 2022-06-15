<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\exists;
use function Klimick\Decode\Constraint\greaterOrEqual;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\listOf;

final class ExistsConstraintTest extends TestCase
{
    public function testDecodeFailedWhenAllItemsInArrayAreInvalid(): void
    {
        $constraint = exists(greaterOrEqual(to: 10));
        $decoder = listOf(int())->constrained($constraint);

        $invalidValid = [7, 8, 9];

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValid, $constraint->metadata()),
            ]),
            actual: decode($invalidValid, $decoder),
        );
    }

    public function testDecodeSuccessWhenAtLeastOneItemInArrayIsValid(): void
    {
        $constraint = exists(greaterOrEqual(to: 10));
        $decoder = listOf(int())->constrained($constraint);

        $validValue = [8, 9, 10];

        Assert::decodeSuccess(
            expected: $validValue,
            actual: decode($validValue, $decoder),
        );
    }
}
