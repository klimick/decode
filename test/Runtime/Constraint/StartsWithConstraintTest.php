<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\startsWith;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\string;

final class StartsWithConstraintTest extends TestCase
{
    public function testFailedWhenStringDoesNotStartWithGivenValue(): void
    {
        $constraint = startsWith('test_');
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = 'test-str';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $valueWithLeadingSpace, $constraint->metadata())
            ]),
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }

    public function testSuccessWhenStringStartsWithGivenValue(): void
    {
        $constraint = startsWith('test_');
        $decoder = string()->constrained($constraint);

        $valueWithLeadingSpace = 'test_str';

        Assert::decodeSuccess(
            expected: $valueWithLeadingSpace,
            actual: decode($valueWithLeadingSpace, $decoder),
        );
    }
}
