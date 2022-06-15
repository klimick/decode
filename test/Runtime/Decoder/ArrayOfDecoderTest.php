<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\arrayOf;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Decoder\union;

final class ArrayOfDecoderTest extends TestCase
{
    public function testTypename(): void
    {
        Assert::name('array<int, string>', arrayOf(int(), string()));
    }

    public function testDecodeFailedWithNonArrayValue(): void
    {
        $k = int();
        $v = string();
        $decoder = arrayOf($k, $v);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$', $decoder->name(), '1')
            ]),
            actual: decode('1', $decoder),
        );
    }

    public function testDecodeFailedWhenKeyIsInvalid(): void
    {
        $k = int();
        $v = string();
        $decoder = arrayOf($k, $v);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$[0]', $v->name(), 1),
            ]),
            actual: decode([1], $decoder),
        );
    }

    public function testDecodeFailedWhenValueIsInvalid(): void
    {
        $k = int();
        $v = string();
        $decoder = arrayOf($k, $v);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$.fst', $k->name(), 'fst'),
            ]),
            actual: decode(['fst' => '1'], $decoder),
        );
    }

    public function testDecodeFailedWithInvalidAliasedKey(): void
    {
        $k = int()->from('$.key');
        $v = string()->from('$.val');
        $decoder = arrayOf($k, $v);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$[0]', $k->name(), 'k1'),
            ]),
            actual: decode([
                ['key' => 'k1', 'val' => 'v1'],
            ], $decoder),
        );
    }

    public function testDecodeFailedWithInvalidAliasedValue(): void
    {
        $k = int()->from('$.key');
        $v = string()->from('$.val');
        $decoder = arrayOf($k, $v);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$[0]', $v->name(), 1),
            ]),
            actual: decode([
                ['key' => 1, 'val' => 1],
            ], $decoder),
        );
    }

    public function testDecodeFailedWhenAliasedKeyIsUndefined(): void
    {
        $k = int()->from('$.key');
        $v = string()->from('$.val');
        $decoder = arrayOf($k, $v);

        $arrayElem = ['_key_' => 1, 'val' => 'fst'];

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport(
                    path: '$[0]',
                    expected: shape(key: int())->name(),
                    actual: $arrayElem,
                )
            ]),
            actual: decode([$arrayElem], $decoder),
        );
    }

    public function testDecodeFailedWhenAliasedValueIsUndefined(): void
    {
        $k = int()->from('$.key');
        $v = string()->from('$.val');
        $decoder = arrayOf($k, $v);

        $arrayElem = ['key' => 1, '_val_' => 'fst'];

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport(
                    path: '$[0]',
                    expected: shape(val: string())->name(),
                    actual: $arrayElem,
                )
            ]),
            actual: decode([$arrayElem], $decoder),
        );
    }

    // todo: think about better report
    //   now error [$[0]]: Type error. Value "fst" cannot be represented as array{key: int} | array{val: string}
    //   should be [$[0]]: Type error. Value "fst" cannot be represented as array{key: int, val: string}
    public function testDecodeFailed(): void
    {
        $k = int()->from('$.key');
        $v = string()->from('$.val');
        $decoder = arrayOf($k, $v);

        $arrayElem = 'fst';

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport(
                    path: '$[0]',
                    expected: union(
                        shape(key: int()),
                        shape(val: string()),
                    )->name(),
                    actual: $arrayElem,
                )
            ]),
            actual: decode([$arrayElem], $decoder),
        );
    }

    public function testDecodeFailedWhenStringToIntImplicitCoerced(): void
    {
        $k = string()->from('$.key');
        $v = int()->from('$.val');
        $decoder = arrayOf($k, $v);

        Assert::decodeFailed(
            expected: new ErrorReport([
                new TypeErrorReport('$[0]', $k->name(), 1),
            ]),
            actual: decode([
                ['key' => '1', 'val' => 42],
            ], $decoder),
        );
    }

    public function testDecodeSimpleArray(): void
    {
        $decoder = arrayOf(int(), string());
        $value = [1 => 'fst', 2 => 'snd', 3 => 'thr'];

        Assert::decodeSuccess(
            expectedValue: $value,
            actualDecoded: decode($value, $decoder),
        );
    }

    public function testDecodeWithAliasedKey(): void
    {
        $shape = shape(key: string(), val: string());
        $array = arrayOf(string()->from('$.key'), $shape);

        Assert::decodeSuccess(
            expectedValue: [
                'k1' => ['key' => 'k1', 'val' => 'fst'],
                'k2' => ['key' => 'k2', 'val' => 'snd'],
                'k3' => ['key' => 'k3', 'val' => 'thr'],
            ],
            actualDecoded: decode([
                ['key' => 'k1', 'val' => 'fst'],
                ['key' => 'k2', 'val' => 'snd'],
                ['key' => 'k3', 'val' => 'thr'],
            ], $array),
        );
    }

    public function testDecodeWithAliasedValue(): void
    {
        $array = arrayOf(int(), string()->from('$.val'));

        Assert::decodeSuccess(
            expectedValue: [
                0 => 'fst',
                1 => 'snd',
                2 => 'thr',
            ],
            actualDecoded: decode([
                ['val' => 'fst'],
                ['val' => 'snd'],
                ['val' => 'thr'],
            ], $array),
        );
    }

    public function testDecodeWithAliasedKeyValue(): void
    {
        $array = arrayOf(string()->from('$.key'), string()->from('$.val'));

        Assert::decodeSuccess(
            expectedValue: [
                'k1' => 'fst',
                'k2' => 'snd',
                'k3' => 'thr',
            ],
            actualDecoded: decode([
                ['key' => 'k1', 'val' => 'fst'],
                ['key' => 'k2', 'val' => 'snd'],
                ['key' => 'k3', 'val' => 'thr'],
            ], $array),
        );
    }

    public function testDecodeWithAliasedKeyAndDefaultValue(): void
    {
        $array = arrayOf(string()->from('$.key'), string()->from('$.val')->default('__default'));

        Assert::decodeSuccess(
            expectedValue: [
                'k1' => 'fst',
                'k2' => '__default',
                'k3' => 'thr',
            ],
            actualDecoded: decode([
                ['key' => 'k1', 'val' => 'fst'],
                ['key' => 'k2'],
                ['key' => 'k3', 'val' => 'thr'],
            ], $array),
        );
    }
}
