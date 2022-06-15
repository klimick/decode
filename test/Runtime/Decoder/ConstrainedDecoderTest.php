<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\andX;
use function Klimick\Decode\Constraint\every;
use function Klimick\Decode\Constraint\maxLength;
use function Klimick\Decode\Constraint\minLength;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\listOf;
use function Klimick\Decode\Decoder\null;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\union;

final class ConstrainedDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('string', string()->constrained(minLength(is: 3)));
    }

    public function testDecodeFailedIfConstraintFailed(): void
    {
        $constraint = every(
            andX(minLength(is: 3), maxLength(is: 10)),
        );
        $decoder = listOf(string())->constrained($constraint);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', ['vi'], $constraint->metadata())
            ]),
            actual: decode(['vi'], $decoder),
        );
    }

    public function testDecodeSuccessIfConstraintSuccess(): void
    {
        $constraint = every(
            andX(minLength(is: 2), maxLength(is: 10)),
        );
        $decoder = listOf(string())->constrained($constraint);

        Assert::decodeSuccess(
            expected: ['vi'],
            actual: decode(['vi'], $decoder),
        );
    }

    public function testConstraintIgnoreNullValues(): void
    {
        $constraint = minLength(is: 2);
        $decoder = union(string(), null())->constrained($constraint);

        Assert::decodeSuccess(
            expected: null,
            actual: decode(null, $decoder),
        );
    }
}
