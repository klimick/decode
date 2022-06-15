<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\nonEmptyString;

final class NonEmptyStringDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('non-empty-string', nonEmptyString());
    }

    public function testDecodeFailed(): void
    {
        $decoder = nonEmptyString();
        $value = '';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), $value),
            ]),
            actual: decode($value, $decoder),
        );
    }

    public function testDecodeSuccess(): void
    {
        $decoder = nonEmptyString();
        $value = 'some non-empty-string value';

        Assert::decodeSuccess(
            expected: $value,
            actual: decode($value, $decoder),
        );
    }
}
