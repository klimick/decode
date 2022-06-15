<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\endsWith;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\string;

final class EndsWithConstraintTest extends TestCase
{
    public function testFailedWhenStringDoesNotStartWithGivenValue(): void
    {
        $constraint = endsWith('_test');
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = 'str-test';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $valueWithLeadingSpace, $constraint->metadata())
            ]),
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }

    public function testSuccessWhenStringStartsWithGivenValue(): void
    {
        $constraint = endsWith('_test');
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = 'str_test';

        Assert::decodeSuccess(
            expected: $valueWithLeadingSpace,
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }
}
