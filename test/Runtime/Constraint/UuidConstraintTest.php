<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Constraint;

use Klimick\Decode\Report\ConstraintErrorReport;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Constraint\uuid;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\string;

final class UuidConstraintTest extends TestCase
{
    public function testDecodeFailedWhenStringIsNotUuid(): void
    {
        $constraint = uuid();
        $decoder = string()->constrained($constraint);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new ConstraintErrorReport('$', 'non-uuid-value', $constraint->metadata())
            ]),
            actual: decode('non-uuid-value', $decoder),
        );
    }

    public function testDecodeSuccessWhenStringIsUuid(): void
    {
        $constraint = uuid();
        $decoder = string()->constrained($constraint);

        $uuidValue = 'c415ac30-31e9-4ac4-94b5-5b6d017b94b7';

        Assert::decodeSuccess(
            expected: $uuidValue,
            actual: decode($uuidValue, $decoder),
        );
    }
}
