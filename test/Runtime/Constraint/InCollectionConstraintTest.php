<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\inCollection;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\listOf;

final class InCollectionConstraintTest extends TestCase
{
    public function testDecodeFailedWhenArrayDoesNotContainRequiredElement(): void
    {
        $constraint = inCollection(42);
        $decoder = listOf(int())->constrained($constraint);

        $value = [40, 41];

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $value, $constraint->metadata())
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenArrayContainsRequiredElement(): void
    {
        $constraint = inCollection(42);
        $decoder = listOf(int())->constrained($constraint);

        $value = [40, 41, 42];

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }
}
