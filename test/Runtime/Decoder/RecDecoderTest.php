<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Runtime\Decoder;

use Klimick\Decode\Decoder\DecoderInterface;
use Klimick\Decode\Report\ErrorReport;
use Klimick\Decode\Report\TypeErrorReport;
use Klimick\Decode\Report\UndefinedErrorReport;
use Klimick\Decode\Test\Runtime\Assert;
use Klimick\Decode\Test\Static\Fixtures\Department;
use PHPUnit\Framework\TestCase;
use function Klimick\Decode\Decoder\decode;
use function Klimick\Decode\Decoder\listOf;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\rec;
use function Klimick\Decode\Decoder\string;

final class RecDecoderTest extends TestCase
{
    /**
     * @return DecoderInterface<Department>
     * @psalm-pure
     */
    private static function getDecoder(): DecoderInterface
    {
        return rec(fn() => object(Department::class)(
            name: string(),
            subDepartments: listOf(self::getDecoder())
        ));
    }

    public function testTypename(): void
    {
        Assert::name(Department::class, self::getDecoder());
    }

    public function testDecodeFailed(): void
    {
        Assert::decodeFailed(
            expectedReport: new ErrorReport([
                new UndefinedErrorReport('$.name'),
                new UndefinedErrorReport('$.subDepartments'),
            ]),
            actualDecoded: decode([], self::getDecoder()),
        );
        Assert::decodeFailed(
            expectedReport: new ErrorReport([
                new TypeErrorReport('$.name', 'string', ['test']),
                new UndefinedErrorReport('$.subDepartments'),
            ]),
            actualDecoded: decode(['name' => ['test']], self::getDecoder()),
        );
        Assert::decodeFailed(
            expectedReport: new ErrorReport([
                new TypeErrorReport('$.name', 'string', ['test']),
                new TypeErrorReport('$.subDepartments', 'list<' . Department::class . '>', 'invalid'),
            ]),
            actualDecoded: decode(['name' => ['test'], 'subDepartments' => 'invalid'], self::getDecoder()),
        );
    }

    public function testDecodeSuccess(): void
    {
        Assert::decodeSuccess(
            expectedValue: new Department('test', []),
            actualDecoded: decode(['name' => 'test', 'subDepartments' => []], self::getDecoder()),
        );
        Assert::decodeSuccess(
            expectedValue: new Department('test', [
                new Department('nested1', []),
                new Department('nested2', []),
                new Department('nested3', []),
            ]),
            actualDecoded: decode(
                ['name' => 'test', 'subDepartments' => [
                    ['name' => 'nested1', 'subDepartments' => []],
                    ['name' => 'nested2', 'subDepartments' => []],
                    ['name' => 'nested3', 'subDepartments' => []],
                ]],
                self::getDecoder(),
            ),
        );
    }
}
