<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\literal;

final class LiteralDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('1', literal(1));
        Assert::name('1.42', literal(1.42));
        Assert::name('true', literal(true));
        Assert::name('false', literal(false));
        Assert::name("'test'", literal('test'));
        Assert::name("'val1' | 'val2'", literal('val1', 'val2'));
    }

    public function testDecodeFailedWithUnexpectedLiteral(): void
    {
        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', '42', 41)
            ]),
            actual: decode(41, literal(42)),
        );
    }

    public function testDecodeSuccessWithExpectedLiteral(): void
    {
        Assert::decodeSuccess(
            expectedValue: 42,
            actualDecoded: decode(42, literal(42)),
        );
    }
}
