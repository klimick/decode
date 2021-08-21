<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\Decoder;

use Fp\Functional\Option\Option;
use Klimick\Decode\Decoder\AbstractDecoder;
use Klimick\Decode\Test\Static\Decoder\Fixtures\Department;
use function Klimick\Decode\Decoder\arrList;
use function Klimick\Decode\Decoder\cast;
use function Klimick\Decode\Decoder\object;
use function Klimick\Decode\Decoder\rec;
use function Klimick\Decode\Decoder\string;
use function Klimick\Decode\Test\Helper\anyValue;

final class RecDecoderTest
{
    /**
     * @psalm-suppress UnusedVariable
     *     todo: &$decoder - false positive
     */
    public function test(): void
    {
        $decoder = rec(function() use (&$decoder) {
            /** @var AbstractDecoder<Department> $self */
            $self = &$decoder;

            return object(Department::class)(
                name: string(),
                subDepartments: arrList($self),
            );
        });

        $decoded = cast(anyValue(), $decoder);
        self::assertTypeDepartment($decoded);
    }

    public function testMisspellPropertyName(): void
    {
        // /** @psalm-suppress DecodeIssue */
        // $_decoded = cast(anyValue(), object(Person::class)(
        //     misspelled_name: string(),
        //     age: int(),
        // ));
    }

    /**
     * @param Option<Department> $_param
     */
    private static function assertTypeDepartment(Option $_param): void
    {
    }
}
