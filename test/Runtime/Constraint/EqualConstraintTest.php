<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\equal;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;

final class EqualConstraintTest extends TestCase
{
    public function testDecodeFailedWhenValueIsNotEqualToRequired(): void
    {
        $constraint = equal(to: 42);
        $decoder = int()->constrained($constraint);

        $value = 40;

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $value, $constraint->metadata())
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccessWhenValueIsEqualToRequired(): void
    {
        $constraint = equal(to: 42);
        $decoder = int()->constrained($constraint);

        $value = 42;

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }
}
