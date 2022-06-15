<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\matchesRegex;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\string;

final class MatchesRegexConstraintTest extends TestCase
{
    public function testDecodeFailedWhenStringDoesNotMatchToGivenRegex(): void
    {
        $constraint = matchesRegex('~\d-\d-\d~');
        $decoder = string()->constrained($constraint);

        $invalidValue = '0-4-a';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', $invalidValue, $constraint->metadata()),
            ]),
            actual: decode($invalidValue, $decoder),
        );
    }

    public function testDecodeSuccessWhenStringMatchesToGivenRegex(): void
    {
        $constraint = matchesRegex('~\d-\d-\d~');
        $decoder = string()->constrained($constraint);

        $validValue = '0-4-2';

        Assert::decodeSuccess(
            expected: $validValue,
            actual: decode($validValue, $decoder),
        );
    }
}
