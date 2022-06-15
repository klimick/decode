<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\bool;
use function Klimick\Decode\Decoder\constantly;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\option;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

final class ShapeDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('array{int, string, bool}', shape(
            int(),
            string(),
            bool(),
        ));

        Assert::name('array{0: int, 1: string, 2?: bool}', shape(
            int(),
            string(),
            bool()->orUndefined(),
        ));

        Assert::name('array{f1: int, f2: string, f3: bool}', shape(
            f1: int(),
            f2: string(),
            f3: bool(),
        ));

        Assert::name('array{f1?: int, f2: string, f3: bool}', shape(
            f1: int()->orUndefined(),
            f2: string(),
            f3: bool(),
        ));
    }

    public function testDecodeFailedWithNonArrayValue(): void
    {
        $decoder = shape(f: string());

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), null)
            ]),
            actual: decode(null, $decoder),
        );
    }

    public function testDecodeFailedWithInvalidPropertyValue(): void
    {
        $decoder = shape(f: string());

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$.f', 'string', 42)
            ]),
            actual: decode(['f' => 42], $decoder),
        );
    }

    public function testDecodeSuccessWithValidPropertyValue(): void
    {
        $decoder = shape(f: string());

        Assert::decodeSuccess(
            expected: ['f' => '42'],
            actual: decode(['f' => '42'], $decoder),
        );
    }

    public function testDecodeSuccessEvenPropertyIsUndefined(): void
    {
        $decoder = shape(f: string()->orUndefined());

        Assert::decodeSuccess(
            expected: [],
            actual: decode([], $decoder),
        );
    }

    public function testDecodeSuccessEvenPropertyIsUndefinedButHasDefaultValue(): void
    {
        $decoder = shape(f: string()->default('42'));

        Assert::decodeSuccess(
            expected: ['f' => '42'],
            actual: decode([], $decoder),
        );
    }

    public function testDecodeSuccessEvenPropertyIsUndefinedButOptional(): void
    {
        $decoder = shape(f: option(string()));

        Assert::decodeSuccess(
            expected: ['f' => Option::none()],
            actual: decode([], $decoder),
        );
    }

    public function testDecodeSuccessWithConstant(): void
    {
        $decoder = shape(
            f: string(),
            a: constantly(true),
        );

        Assert::decodeSuccess(
            expected: ['f' => '42', 'a' => true],
            actual: decode(['f' => '42'], $decoder),
        );
    }

    public function testDecodeSuccessWithAlias(): void
    {
        $decoder = shape(f: string()->from('$.nested.value'));

        Assert::decodeSuccess(
            expected: ['f' => '42'],
            actual: decode(['nested' => ['value' => '42']], $decoder),
        );
    }

    public function testDecodeFailedWhenAliasedValueIsNotShape(): void
    {
        $decoder = shape(f: string()->from('$.nested.value'));

        Assert::decodeFailed(
            expected: new ErrorReport([
                new UndefinedErrorReport('$.f', ['$.nested.value']),
            ]),
            actual: decode(['nested' => '42'], $decoder),
        );
    }

    public function testPickPropsFromShape(): void
    {
        $decoder = shape(
            a: string(),
            b: string(),
            c: string(),
        );

        Assert::decodeSuccess(
            expected: ['a' => '_', 'b' => '_'],
            actual: decode(['a' => '_', 'b' => '_', 'c' => '_'], $decoder->pick(['a', 'b'])),
        );
    }

    public function testOmitPropsFromShape(): void
    {
        $decoder = shape(
            a: string(),
            b: string(),
            c: string(),
        );

        Assert::decodeSuccess(
            expected: ['c' => '_'],
            actual: decode(['a' => '_', 'b' => '_', 'c' => '_'], $decoder->omit(['a', 'b'])),
        );
    }
}
