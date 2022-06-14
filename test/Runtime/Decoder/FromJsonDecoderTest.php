<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\fromJson;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

final class FromJsonDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('array{test: string}', fromJson(
            shape(test: string()),
        ));
    }

    public function testDecodeFailedWithNonStringValue(): void
    {
        $decoder = fromJson(
            shape(test: string()),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), 42),
            ]),
            actual: decode(42, $decoder),
        );
    }

    public function testDecodeFailedWhenJsonSyntaxError(): void
    {
        $decoder = fromJson(
            shape(test: string()),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), '{"test": '),
            ]),
            actual: decode('{"test": ', $decoder),
        );
    }

    public function testDecodeFailedWhenNestedDecoderFailed(): void
    {
        $decoder = fromJson(
            shape(test: string()),
        );

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$.test', 'string', 1),
            ]),
            actual: decode('{"test": 1}', $decoder),
        );
    }
}
