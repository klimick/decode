<?php

declare(strict_types=1);

namespace Klimick\Decode\Test\Static\HighOrder;

use function Klimick\Decode\Decoder\int;
use function Klimick\Decode\Decoder\shape;
use function Klimick\Decode\Decoder\string;

final class HighOrderIssueTest
{
    public function testWithOptionalPropertyWithDefaultValue(): void
    {
        /** @psalm-suppress OptionalCallContradictionIssue */
        $_shape = shape(
            prop: int()->default(42)->optional(),
        );
    }

    public function testWithAliasedProperty(): void
    {
        /** @psalm-suppress InvalidPropertyAliasIssue */
        $_shape = shape(
            prop: int()->from('person_age'),
        );
    }

    public function testOptionalCannotBeCalledMoreThanOnce(): void
    {
        /** @psalm-suppress BrandAlreadyDefinedIssue */
        $_shape = shape(
            prop: string()->optional()->optional(),
        );
    }

    public function testFromCannotBeCalledMoreThanOnce(): void
    {
        /** @psalm-suppress BrandAlreadyDefinedIssue */
        $_shape = shape(
            prop: string()->from('$.another_prop')->from('$.another_prop'),
        );
    }
}
